require("dotenv").config();

const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const Redis = require("ioredis");

const PORT = process.env.SOCKET_PORT || 3001;
const REDIS_URL = process.env.REDIS_URL || "redis://redis:6379";

// ── Express app ───────────────────────────────────────────────────
const app = express();
app.get("/health", (_req, res) => res.json({ status: "ok" }));

const server = http.createServer(app);

// ── Redis pub/sub ─────────────────────────────────────────────────
const pubClient = new Redis(REDIS_URL);
const subClient = pubClient.duplicate();

pubClient.on("error", (err) => console.error("[Redis pub] error:", err));
subClient.on("error", (err) => console.error("[Redis sub] error:", err));

// ── Socket.io ─────────────────────────────────────────────────────
const io = new Server(server, {
  cors: {
    origin: process.env.CORS_ORIGIN
      ? process.env.CORS_ORIGIN.split(",")
      : ["https://zslab-shop.duckdns.org", "https://zslab-stg.duckdns.org"],
    methods: ["GET", "POST"],
    credentials: true,
  },
  transports: ["websocket", "polling"],
});

// Middleware: simple token check
io.use((socket, next) => {
  const token = socket.handshake.auth?.token;
  if (!token) {
    // Allow guest connections; attach guest flag
    socket.data.userId = null;
    socket.data.isGuest = true;
  } else {
    // TODO: validate JWT token against Laravel API
    socket.data.token = token;
    socket.data.isGuest = false;
  }
  next();
});

io.on("connection", (socket) => {
  console.log(`[socket] connected  sid=${socket.id} guest=${socket.data.isGuest}`);

  // ── Cart ────────────────────────────────────────────────────────
  socket.on("cart:join", (sessionId) => {
    socket.join(`cart:${sessionId}`);
  });

  socket.on("cart:update", (data) => {
    io.to(`cart:${data.sessionId}`).emit("cart:updated", data);
  });

  // ── Order status ────────────────────────────────────────────────
  socket.on("order:subscribe", (orderId) => {
    socket.join(`order:${orderId}`);
  });

  // ── Notifications ───────────────────────────────────────────────
  socket.on("user:join", (userId) => {
    socket.join(`user:${userId}`);
  });

  socket.on("disconnect", (reason) => {
    console.log(`[socket] disconnected sid=${socket.id} reason=${reason}`);
  });
});

// ── Redis → Socket.io bridge ──────────────────────────────────────
subClient.subscribe("zslab:events", (err) => {
  if (err) console.error("[Redis] subscribe error:", err);
});

subClient.on("message", (_channel, message) => {
  try {
    const event = JSON.parse(message);
    const { room, name, data } = event;
    if (room && name) {
      io.to(room).emit(name, data);
    }
  } catch (e) {
    console.error("[Redis] invalid event payload:", message);
  }
});

// ── Start ─────────────────────────────────────────────────────────
server.listen(PORT, () => {
  console.log(`[zslab-socket] listening on :${PORT}`);
});

process.on("SIGTERM", () => {
  server.close(() => process.exit(0));
});
