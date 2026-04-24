@extends('admin.layouts.app')

@section('title', '1:1 문의 관리')

@push('styles')
<style>
#chat-panel { height: calc(100vh - 200px); display: flex; gap: 0; }
#room-list { width: 320px; border-right: 1px solid #dee2e6; overflow-y: auto; flex-shrink: 0; }
#chat-area { flex: 1; display: flex; flex-direction: column; }
#messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 8px; background: #f8f9fa; }
.msg-bubble { max-width: 70%; padding: 8px 12px; border-radius: 12px; font-size: 14px; line-height: 1.4; }
.msg-user  .msg-bubble { background: #fff; border: 1px solid #dee2e6; align-self: flex-start; }
.msg-admin .msg-bubble { background: #4f46e5; color: #fff; align-self: flex-end; }
.msg-row { display: flex; flex-direction: column; }
.msg-user  { align-items: flex-start; }
.msg-admin { align-items: flex-end; }
.msg-meta { font-size: 11px; color: #999; margin-top: 2px; }
.typing-dot { display: inline-block; width: 6px; height: 6px; background: #999; border-radius: 50%; margin: 0 1px; animation: bounce 1.2s infinite ease-in-out; }
.typing-dot:nth-child(2) { animation-delay: .2s; }
.typing-dot:nth-child(3) { animation-delay: .4s; }
@keyframes bounce { 0%,80%,100%{transform:translateY(0)} 40%{transform:translateY(-5px)} }
.room-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; transition: background .15s; }
.room-item:hover, .room-item.active { background: #f0f4ff; }
.unread-badge { background: #ef4444; color: #fff; font-size: 11px; border-radius: 10px; padding: 1px 6px; }
</style>
@endpush

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1 class="m-0">1:1 문의 관리</h1></div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="card" style="height:calc(100vh - 180px)">
      <div class="card-body p-0" id="chat-panel">

        {{-- ── 문의 목록 ─── --}}
        <div id="room-list">
          <div class="p-3 border-bottom bg-white">
            <strong>문의 목록</strong>
            <span id="total-badge" class="badge bg-secondary ms-1">{{ count($rooms) }}</span>
          </div>
          @forelse($rooms as $room)
          <div class="room-item {{ $loop->first ? 'active' : '' }}"
               data-room-id="{{ $room->id }}"
               onclick="selectRoom({{ $room->id }}, '{{ addslashes($room->user_name ?? '알 수 없음') }}')">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold" style="font-size:14px;">{{ $room->user_name ?? '알 수 없음' }}</div>
                <div class="text-muted" style="font-size:12px;">{{ $room->user_email ?? '' }}</div>
                @if($room->last_message)
                <div class="text-truncate text-muted mt-1" style="font-size:12px;max-width:220px;">{{ $room->last_message }}</div>
                @endif
              </div>
              <div class="text-end">
                @if($room->unread_count > 0)
                <span class="unread-badge" id="badge-{{ $room->id }}">{{ $room->unread_count }}</span>
                @else
                <span class="unread-badge d-none" id="badge-{{ $room->id }}">0</span>
                @endif
                <div class="text-muted mt-1" style="font-size:11px;">
                  {{ $room->last_message_at ? \Carbon\Carbon::parse($room->last_message_at)->diffForHumans() : '' }}
                </div>
              </div>
            </div>
          </div>
          @empty
          <div class="p-4 text-center text-muted">문의가 없습니다.</div>
          @endforelse
        </div>

        {{-- ── 채팅 영역 ─── --}}
        <div id="chat-area">
          <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
            <span id="chat-title" class="fw-semibold">문의를 선택하세요</span>
            <span id="conn-status" class="badge bg-secondary">연결 안됨</span>
          </div>
          <div id="messages"></div>
          <div id="typing-row" class="px-4 py-1" style="min-height:24px; display:none;">
            <span class="text-muted" style="font-size:13px;">입력 중</span>
            <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
          </div>
          <form id="send-form" class="p-3 border-top bg-white d-flex gap-2" onsubmit="sendMsg(event)">
            <input id="msg-input" type="text" class="form-control" placeholder="메시지를 입력하세요..." disabled>
            <button type="submit" class="btn btn-primary" id="send-btn" disabled>전송</button>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
<script>
const ADMIN_TOKEN = '{{ $adminToken }}';
let socket = null;
let currentRoomId = null;
let typingTimer = null;

function connectSocket() {
  if (socket) socket.disconnect();
  socket = io(window.location.origin, {
    auth: { token: ADMIN_TOKEN },
    transports: ['websocket', 'polling'],
    path: '/chat/socket.io',
  });
  socket.on('connect', () => {
    document.getElementById('conn-status').textContent = '연결됨';
    document.getElementById('conn-status').className   = 'badge bg-success';
    if (currentRoomId) joinRoom(currentRoomId);
  });
  socket.on('disconnect', () => {
    document.getElementById('conn-status').textContent = '연결 끊김';
    document.getElementById('conn-status').className   = 'badge bg-danger';
  });
  socket.on('room_joined', ({ room, messages }) => {
    renderMessages(messages);
    document.getElementById('msg-input').disabled = false;
    document.getElementById('send-btn').disabled  = false;
    clearBadge(room.id);
    socket.emit('mark_read', { roomId: room.id });
  });
  socket.on('message_received', (msg) => {
    appendMessage(msg);
    if (msg.sender_type === 'user' && currentRoomId === msg.roomId) {
      socket.emit('mark_read', { roomId: currentRoomId });
      clearBadge(currentRoomId);
    }
  });
  socket.on('typing', ({ isTyping }) => {
    document.getElementById('typing-row').style.display = isTyping ? 'block' : 'none';
    scrollBottom();
  });
}

function joinRoom(roomId) {
  socket.emit('join_room', { roomId, adminId: 'admin' });
}

function selectRoom(roomId, userName) {
  currentRoomId = roomId;
  document.getElementById('chat-title').textContent = userName + ' 님의 문의';
  document.querySelectorAll('.room-item').forEach(el =>
    el.classList.toggle('active', parseInt(el.dataset.roomId) === roomId)
  );
  document.getElementById('messages').innerHTML = '';
  if (socket && socket.connected) joinRoom(roomId);
  else connectSocket();
}

function renderMessages(msgs) {
  const el = document.getElementById('messages');
  el.innerHTML = '';
  msgs.forEach(m => appendMessage(m, false));
  scrollBottom();
}

function appendMessage(msg, scroll = true) {
  const el = document.getElementById('messages');
  const isAdmin = msg.sender_type === 'admin';
  const div = document.createElement('div');
  div.className = 'msg-row ' + (isAdmin ? 'msg-admin' : 'msg-user');
  const time = new Date(msg.created_at).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' });
  div.innerHTML =
    '<div class="msg-bubble">' + escapeHtml(msg.message) + '</div>' +
    '<div class="msg-meta">' + time + (isAdmin && msg.is_read ? ' ✓읽음' : '') + '</div>';
  el.appendChild(div);
  if (scroll) scrollBottom();
}

function sendMsg(e) {
  e.preventDefault();
  const input = document.getElementById('msg-input');
  const text  = input.value.trim();
  if (!text || !socket || !currentRoomId) return;
  socket.emit('send_message', { roomId: currentRoomId, message: text });
  input.value = '';
  socket.emit('typing_stop', { roomId: currentRoomId });
}

document.getElementById('msg-input').addEventListener('input', () => {
  if (!socket || !currentRoomId) return;
  socket.emit('typing_start', { roomId: currentRoomId });
  clearTimeout(typingTimer);
  typingTimer = setTimeout(() => socket.emit('typing_stop', { roomId: currentRoomId }), 1500);
});

function scrollBottom() {
  const el = document.getElementById('messages');
  el.scrollTop = el.scrollHeight;
}

function clearBadge(roomId) {
  const badge = document.getElementById('badge-' + roomId);
  if (badge) { badge.textContent = '0'; badge.classList.add('d-none'); }
}

function escapeHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

document.addEventListener('DOMContentLoaded', () => {
  connectSocket();
  const first = document.querySelector('.room-item');
  if (first) first.click();
});
</script>
@endpush
