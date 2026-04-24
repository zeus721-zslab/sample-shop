'use client'

import { useEffect, useRef } from 'react'
import { useChat } from '@/hooks/useChat'

interface Props {
  chatToken: string
  roomId: number
  onClose: () => void
  onRead: () => void
}

export default function ChatWindow({ chatToken, roomId, onClose, onRead }: Props) {
  const { connected, messages, isTyping, sendMessage, sendTypingStart, sendTypingStop } =
    useChat(chatToken, roomId)

  const bottomRef = useRef<HTMLDivElement>(null)
  const inputRef  = useRef<HTMLInputElement>(null)

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, isTyping])

  useEffect(() => {
    if (connected) onRead()
  }, [connected, onRead])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const val = inputRef.current?.value.trim()
    if (!val) return
    sendMessage(val)
    if (inputRef.current) inputRef.current.value = ''
    sendTypingStop()
  }

  return (
    <div className="w-[340px] h-[500px] bg-white rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.14)] flex flex-col overflow-hidden border border-gray-100">

      {/* Header */}
      <div className="flex items-center justify-between px-4 py-3.5 bg-[#111] text-white shrink-0">
        <div>
          <p className="font-semibold text-sm tracking-tight">1:1 문의</p>
          <p className="text-[11px] text-white/60 mt-0.5 flex items-center gap-1.5">
            <span
              className={`inline-block w-1.5 h-1.5 rounded-full ${connected ? 'bg-emerald-400' : 'bg-white/30'}`}
            />
            {connected ? '연결됨' : '연결 중…'}
          </p>
        </div>
        <button
          onClick={onClose}
          aria-label="닫기"
          className="w-7 h-7 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors text-white/70 hover:text-white"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto px-4 py-3 flex flex-col gap-2 scrollbar-none">
        {messages.length === 0 && (
          <div className="flex-1 flex flex-col items-center justify-center gap-2 text-gray-400 select-none">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="opacity-30">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            <p className="text-xs text-center leading-relaxed">
              안녕하세요!<br />무엇이 궁금하신가요?
            </p>
          </div>
        )}

        {messages.map(msg => {
          const isMine = msg.sender_type === 'user'
          return (
            <div
              key={msg.id}
              className={`flex flex-col ${isMine ? 'items-end' : 'items-start'}`}
            >
              <div
                className={`max-w-[75%] px-3.5 py-2 rounded-2xl text-[13px] leading-[1.5] ${
                  isMine
                    ? 'bg-[#111] text-white rounded-br-sm'
                    : 'bg-gray-100 text-[#111] rounded-bl-sm'
                }`}
              >
                {msg.message}
              </div>
              <div className="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                {new Date(msg.created_at).toLocaleTimeString('ko-KR', {
                  hour: '2-digit',
                  minute: '2-digit',
                })}
                {isMine && msg.is_read && (
                  <span className="text-[10px] text-gray-400">읽음</span>
                )}
              </div>
            </div>
          )
        })}

        {/* Typing indicator */}
        {isTyping && (
          <div className="flex items-center gap-1 px-3.5 py-2.5 bg-gray-100 rounded-2xl rounded-bl-sm w-fit">
            {[0, 0.2, 0.4].map((d, i) => (
              <span
                key={i}
                className="w-1.5 h-1.5 bg-gray-400 rounded-full chat-bounce"
                style={{ animationDelay: `${d}s` }}
              />
            ))}
          </div>
        )}

        <div ref={bottomRef} />
      </div>

      {/* Input */}
      <form
        onSubmit={handleSubmit}
        className="flex items-center gap-2 px-3 py-2.5 border-t border-gray-100 shrink-0"
      >
        <input
          ref={inputRef}
          type="text"
          placeholder={connected ? '메시지를 입력하세요…' : '연결 중…'}
          disabled={!connected}
          onChange={sendTypingStart}
          className="flex-1 border border-gray-200 rounded-full px-4 py-2 text-[13px] outline-none focus:border-[#111] transition-colors disabled:bg-gray-50 disabled:cursor-not-allowed placeholder:text-gray-400"
        />
        <button
          type="submit"
          disabled={!connected}
          className="w-9 h-9 rounded-full bg-[#111] text-white flex items-center justify-center shrink-0 disabled:bg-gray-200 disabled:cursor-not-allowed transition-colors hover:bg-[#333]"
          aria-label="전송"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <line x1="22" y1="2" x2="11" y2="13" />
            <polygon points="22 2 15 22 11 13 2 9 22 2" />
          </svg>
        </button>
      </form>
    </div>
  )
}
