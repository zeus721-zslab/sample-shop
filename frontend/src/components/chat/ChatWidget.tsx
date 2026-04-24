'use client'

import { AnimatePresence, motion } from 'framer-motion'
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
    <div className="fixed bottom-6 right-6 z-[9999] flex flex-col items-end gap-3">
      {/* Chat window — slide-up animation */}
      <AnimatePresence>
        {open && chatToken && roomId && (
          <motion.div
            key="chat-window"
            initial={{ opacity: 0, y: 16, scale: 0.96 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 16, scale: 0.96 }}
            transition={{ duration: 0.22, ease: [0.32, 0.72, 0, 1] }}
            style={{ transformOrigin: 'bottom right' }}
          >
            <ChatWindow
              chatToken={chatToken}
              roomId={roomId}
              onClose={() => setOpen(false)}
              onRead={() => setUnread(0)}
            />
          </motion.div>
        )}
      </AnimatePresence>

      {/* Trigger button */}
      <motion.button
        onClick={() => setOpen(v => !v)}
        aria-label="1:1 문의 채팅"
        whileHover={{ scale: 1.06 }}
        whileTap={{ scale: 0.94 }}
        className="relative flex items-center justify-center w-14 h-14 rounded-full bg-[#111] text-white shadow-[0_4px_20px_rgba(0,0,0,0.28)] cursor-pointer border-0"
      >
        <AnimatePresence mode="wait" initial={false}>
          {open ? (
            <motion.span
              key="close"
              initial={{ opacity: 0, rotate: -45 }}
              animate={{ opacity: 1, rotate: 0 }}
              exit={{ opacity: 0, rotate: 45 }}
              transition={{ duration: 0.18 }}
              className="flex items-center justify-center"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg>
            </motion.span>
          ) : (
            <motion.span
              key="chat"
              initial={{ opacity: 0, rotate: 45 }}
              animate={{ opacity: 1, rotate: 0 }}
              exit={{ opacity: 0, rotate: -45 }}
              transition={{ duration: 0.18 }}
              className="flex items-center justify-center"
            >
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z" />
              </svg>
            </motion.span>
          )}
        </AnimatePresence>

        {/* Unread badge */}
        <AnimatePresence>
          {unread > 0 && !open && (
            <motion.span
              key="badge"
              initial={{ scale: 0 }}
              animate={{ scale: 1 }}
              exit={{ scale: 0 }}
              transition={{ type: 'spring', stiffness: 500, damping: 28 }}
              className="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full px-1.5 py-px min-w-[18px] text-center leading-[16px]"
            >
              {unread > 99 ? '99+' : unread}
            </motion.span>
          )}
        </AnimatePresence>
      </motion.button>
    </div>
  )
}
