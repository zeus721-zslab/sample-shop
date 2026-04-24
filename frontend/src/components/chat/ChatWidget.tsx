'use client'

import { useEffect, useState } from 'react'
import { useAuth } from '@/store/auth'
import ChatWindow from './ChatWindow'

export default function ChatWidget() {
  const { user, token, isLoaded } = useAuth()
  const [open, setOpen] = useState(false)
  const [chatToken, setChatToken] = useState<string | null>(null)
  const [roomId, setRoomId] = useState<number | null>(null)
  const [unread, setUnread] = useState(0)

  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? ''

  useEffect(() => {
    if (!isLoaded || !user || !token) {
      setChatToken(null)
      setRoomId(null)
      setUnread(0)
      return
    }

    // 채팅 토큰 + 채팅방 초기화
    const init = async () => {
      try {
        const [tokenRes, roomRes] = await Promise.all([
          fetch(`${apiUrl}/chat/token`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
          }),
          fetch(`${apiUrl}/chat/rooms`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
          }),
        ])
        if (tokenRes.ok && roomRes.ok) {
          const { token: ct } = await tokenRes.json()
          const { room, unread: u } = await roomRes.json()
          setChatToken(ct)
          setRoomId(room.id)
          setUnread(u)
        }
      } catch {}
    }
    init()
  }, [user, token, isLoaded, apiUrl])

  if (!isLoaded || !user) return null

  return (
    <>
      {open && chatToken && roomId && (
        <ChatWindow
          chatToken={chatToken}
          roomId={roomId}
          onClose={() => setOpen(false)}
          onRead={() => setUnread(0)}
        />
      )}

      <button
        onClick={() => setOpen(v => !v)}
        aria-label="1:1 문의 채팅"
        style={{
          position: 'fixed',
          bottom: '24px',
          right: '24px',
          zIndex: 9999,
          width: '56px',
          height: '56px',
          borderRadius: '50%',
          background: '#4f46e5',
          border: 'none',
          boxShadow: '0 4px 16px rgba(79,70,229,.4)',
          cursor: 'pointer',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          transition: 'transform .2s',
        }}
        onMouseEnter={e => (e.currentTarget.style.transform = 'scale(1.1)')}
        onMouseLeave={e => (e.currentTarget.style.transform = 'scale(1)')}
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" fill="white" />
        </svg>
        {unread > 0 && (
          <span style={{
            position: 'absolute',
            top: '-4px',
            right: '-4px',
            background: '#ef4444',
            color: '#fff',
            fontSize: '11px',
            fontWeight: 700,
            borderRadius: '10px',
            padding: '1px 5px',
            minWidth: '18px',
            textAlign: 'center',
          }}>
            {unread > 99 ? '99+' : unread}
          </span>
        )}
      </button>
    </>
  )
}
