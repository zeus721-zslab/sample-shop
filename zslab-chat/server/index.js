'use strict';

const express   = require('express');
const { Server } = require('socket.io');
const http      = require('http');
const mysql     = require('mysql2/promise');
const Redis     = require('ioredis');
const jwt       = require('jsonwebtoken');

// ── 환경변수 ──────────────────────────────────────────────────────────────────
const PORT       = parseInt(process.env.PORT ?? '3001', 10);
const JWT_SECRET = process.env.JWT_SECRET ?? 'zslab-chat-secret';
const REDIS_URL  = process.env.REDIS_URL   ?? 'redis://redis:6379';
const DB_CONFIG  = {
  host    : process.env.DB_HOST     ?? 'mariadb',
  port    : parseInt(process.env.DB_PORT ?? '3306', 10),
  database: process.env.DB_DATABASE ?? 'zslab_shop',
  user    : process.env.DB_USERNAME ?? 'zslab',
  password: process.env.DB_PASSWORD ?? '',
  waitForConnections: true,
  connectionLimit   : 10,
};

// ── DB / Redis ────────────────────────────────────────────────────────────────
let pool;
let pub, sub;

async function initDB() {
  pool = mysql.createPool(DB_CONFIG);
  // 연결 확인
  const conn = await pool.getConnection();
  conn.release();
  console.log('[DB] MariaDB connected');
}

function initRedis() {
  pub = new Redis(REDIS_URL);
  sub = new Redis(REDIS_URL);
  pub.on('error', e => console.error('[Redis pub]', e.message));
  sub.on('error', e => console.error('[Redis sub]', e.message));
  sub.subscribe('chat:broadcast', (err) => {
    if (err) console.error('[Redis sub] subscribe error:', err);
  });
  // Redis pub/sub → 해당 room 소켓에 브로드캐스트
  sub.on('message', (_channel, data) => {
    try {
      const parsed = JSON.parse(data);
      if (parsed.roomId) {
        io.to(`room:${parsed.roomId}`).emit(parsed.event, parsed.payload);
      }
    } catch {}
  });
  console.log('[Redis] connected');
}

// ── Express + Socket.io ───────────────────────────────────────────────────────
const app    = express();
const server = http.createServer(app);
const io     = new Server(server, {
  path : '/socket.io',
  cors : { origin: '*', methods: ['GET', 'POST'] },
  transports: ['websocket', 'polling'],
});

app.get('/health', (_req, res) => res.json({ status: 'ok', service: 'zslab-chat' }));

// ── JWT 인증 미들웨어 ─────────────────────────────────────────────────────────
io.use((socket, next) => {
  const token = socket.handshake.auth?.token;
  if (!token) return next(new Error('unauthorized'));
  try {
    const payload = jwt.verify(token, JWT_SECRET);
    socket.userId   = payload.userId;
    socket.userType = payload.userType ?? 'user';
    next();
  } catch {
    next(new Error('unauthorized'));
  }
});

function broadcast(roomId, event, payload) {
  pub.publish('chat:broadcast', JSON.stringify({ roomId, event, payload }));
}

// ── 소켓 이벤트 ──────────────────────────────────────────────────────────────
io.on('connection', (socket) => {
  console.log(`[Socket] connect uid=${socket.userId} type=${socket.userType}`);

  // 채팅방 입장
  socket.on('join_room', async ({ roomId }) => {
    if (!roomId) return;

    // 권한 확인
    const [rows] = await pool.query(
      'SELECT * FROM chat_participants WHERE room_id = ? AND user_id = ?',
      [roomId, socket.userId]
    );

    // admin은 모든 방 접근 가능, user는 참가자인 방만
    if (socket.userType !== 'admin' && rows.length === 0) {
      socket.emit('error', { message: 'Forbidden' });
      return;
    }

    // admin이 처음 접근하면 참가자 등록
    if (socket.userType === 'admin' && rows.length === 0) {
      await pool.query(
        'INSERT IGNORE INTO chat_participants (room_id, user_id, user_type, joined_at) VALUES (?, ?, "admin", NOW())',
        [roomId, socket.userId]
      );
    }

    socket.join(`room:${roomId}`);
    socket.currentRoomId = roomId;

    // 방 + 메시지 조회
    const [[room]]    = await pool.query('SELECT * FROM chat_rooms WHERE id = ?', [roomId]);
    const [messages]  = await pool.query(
      'SELECT * FROM chat_messages WHERE room_id = ? ORDER BY created_at ASC LIMIT 100',
      [roomId]
    );

    socket.emit('room_joined', { room, messages });
  });

  // 메시지 전송
  socket.on('send_message', async ({ roomId, message }) => {
    if (!roomId || !message?.trim()) return;

    const [result] = await pool.query(
      'INSERT INTO chat_messages (room_id, sender_id, sender_type, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())',
      [roomId, socket.userId, socket.userType, message.trim()]
    );

    const [[msg]] = await pool.query('SELECT * FROM chat_messages WHERE id = ?', [result.insertId]);
    broadcast(roomId, 'message_received', msg);
  });

  // 읽음 처리
  socket.on('mark_read', async ({ roomId }) => {
    if (!roomId) return;
    const oppositeType = socket.userType === 'admin' ? 'user' : 'admin';
    await pool.query(
      'UPDATE chat_messages SET is_read = 1 WHERE room_id = ? AND sender_type = ? AND is_read = 0',
      [roomId, oppositeType]
    );
    // 참가자 last_read_at 업데이트
    await pool.query(
      'UPDATE chat_participants SET last_read_at = NOW() WHERE room_id = ? AND user_id = ?',
      [roomId, socket.userId]
    );
  });

  // 타이핑 시작
  socket.on('typing_start', ({ roomId }) => {
    if (!roomId) return;
    socket.to(`room:${roomId}`).emit('typing', { isTyping: true, userId: socket.userId });
  });

  // 타이핑 종료
  socket.on('typing_stop', ({ roomId }) => {
    if (!roomId) return;
    socket.to(`room:${roomId}`).emit('typing', { isTyping: false, userId: socket.userId });
  });

  socket.on('disconnect', () => {
    console.log(`[Socket] disconnect uid=${socket.userId}`);
  });
});

// ── 서버 기동 ─────────────────────────────────────────────────────────────────
(async () => {
  // DB/Redis 연결 재시도 (컨테이너 시작 순서 대기)
  for (let i = 0; i < 10; i++) {
    try {
      await initDB();
      initRedis();
      break;
    } catch (e) {
      console.warn(`[Boot] retry ${i + 1}/10: ${e.message}`);
      await new Promise(r => setTimeout(r, 3000));
    }
  }

  server.listen(PORT, '0.0.0.0', () => {
    console.log(`[zslab-chat] listening on :${PORT}`);
  });
})();
