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
    <div style={{
      position: 'fixed',
      bottom: '90px',
      right: '24px',
      zIndex: 9998,
      width: '340px',
      height: '480px',
      background: '#fff',
      borderRadius: '16px',
      boxShadow: '0 8px 32px rgba(0,0,0,.16)',
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden',
    }}>
      {/* Header */}
      <div style={{
        padding: '14px 16px',
        background: '#4f46e5',
        color: '#fff',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
      }}>
        <div>
          <div style={{ fontWeight: 600, fontSize: '15px' }}>1:1 문의</div>
          <div style={{ fontSize: '11px', opacity: .8 }}>
            {connected ? '연결됨' : '연결 중...'}
          </div>
        </div>
        <button
          onClick={onClose}
          style={{ background: 'none', border: 'none', color: '#fff', cursor: 'pointer', fontSize: '18px' }}
        >✕</button>
      </div>

      {/* Messages */}
      <div style={{ flex: 1, overflowY: 'auto', padding: '12px', display: 'flex', flexDirection: 'column', gap: '8px' }}>
        {messages.length === 0 && (
          <p style={{ textAlign: 'center', color: '#aaa', fontSize: '13px', marginTop: '40px' }}>
            안녕하세요! 무엇이 궁금하신가요?
          </p>
        )}
        {messages.map(msg => {
          const isMine = msg.sender_type === 'user'
          return (
            <div key={msg.id} style={{ display: 'flex', flexDirection: 'column', alignItems: isMine ? 'flex-end' : 'flex-start' }}>
              <div style={{
                maxWidth: '75%',
                padding: '8px 12px',
                borderRadius: '12px',
                fontSize: '13px',
                lineHeight: 1.45,
                background: isMine ? '#4f46e5' : '#f3f4f6',
                color: isMine ? '#fff' : '#111',
              }}>
                {msg.message}
              </div>
              <div style={{ fontSize: '10px', color: '#bbb', marginTop: '2px' }}>
                {new Date(msg.created_at).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' })}
                {isMine && msg.is_read && ' ✓읽음'}
              </div>
            </div>
          )
        })}
        {isTyping && (
          <div style={{ display: 'flex', gap: '4px', padding: '8px 12px', background: '#f3f4f6', borderRadius: '12px', width: 'fit-content' }}>
            {[0, .2, .4].map((d, i) => (
              <span key={i} style={{
                width: '7px', height: '7px', background: '#999', borderRadius: '50%',
                display: 'inline-block',
                animation: 'chatBounce 1.2s infinite ease-in-out',
                animationDelay: `${d}s`,
              }} />
            ))}
          </div>
        )}
        <div ref={bottomRef} />
      </div>

      {/* Input */}
      <form onSubmit={handleSubmit} style={{
        display: 'flex', gap: '8px', padding: '10px 12px', borderTop: '1px solid #e5e7eb',
      }}>
        <input
          ref={inputRef}
          type="text"
          placeholder="메시지를 입력하세요..."
          disabled={!connected}
          onChange={() => { sendTypingStart() }}
          style={{
            flex: 1, border: '1px solid #d1d5db', borderRadius: '20px',
            padding: '8px 14px', fontSize: '13px', outline: 'none',
          }}
        />
        <button
          type="submit"
          disabled={!connected}
          style={{
            padding: '8px 14px', borderRadius: '20px', border: 'none',
            background: '#4f46e5', color: '#fff', fontSize: '13px', cursor: 'pointer',
          }}
        >전송</button>
      </form>

      <style>{`
        @keyframes chatBounce {
          0%,80%,100% { transform: translateY(0); }
          40%          { transform: translateY(-5px); }
        }
      `}</style>
    </div>
  )
}
