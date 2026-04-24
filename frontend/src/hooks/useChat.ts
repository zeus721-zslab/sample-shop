'use client'

import { useEffect, useRef, useState, useCallback } from 'react'
import { io, Socket } from 'socket.io-client'

export interface ChatMessage {
  id: number
  room_id: number
  sender_id: string
  sender_type: 'user' | 'admin'
  message: string
  is_read: boolean
  created_at: string
}

export interface ChatRoom {
  id: number
  type: '1to1' | 'group'
  name: string | null
  is_active: boolean
  created_at: string
}

export function useChat(token: string | null, roomId?: number) {
  const socketRef = useRef<Socket | null>(null)
  const [connected, setConnected] = useState(false)
  const [room, setRoom] = useState<ChatRoom | null>(null)
  const [messages, setMessages] = useState<ChatMessage[]>([])
  const [isTyping, setIsTyping] = useState(false)
  const typingTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  const chatUrl = process.env.NEXT_PUBLIC_CHAT_URL ?? ''

  useEffect(() => {
    if (!token || !chatUrl) return

    const socket = io(chatUrl, {
      auth: { token },
      transports: ['websocket', 'polling'],
      path: '/chat/socket.io',
    })
    socketRef.current = socket

    socket.on('connect', () => {
      setConnected(true)
      if (roomId) socket.emit('join_room', { roomId })
    })
    socket.on('disconnect', () => setConnected(false))

    socket.on('room_joined', ({ room, messages }: { room: ChatRoom; messages: ChatMessage[] }) => {
      setRoom(room)
      setMessages(messages)
      socket.emit('mark_read', { roomId: room.id })
    })

    socket.on('message_received', (msg: ChatMessage) => {
      setMessages(prev => [...prev, msg])
    })

    socket.on('typing', ({ isTyping: t }: { isTyping: boolean }) => {
      setIsTyping(t)
      if (t) {
        if (typingTimeoutRef.current) clearTimeout(typingTimeoutRef.current)
        typingTimeoutRef.current = setTimeout(() => setIsTyping(false), 5000)
      } else {
        if (typingTimeoutRef.current) clearTimeout(typingTimeoutRef.current)
      }
    })

    return () => {
      socket.disconnect()
      if (typingTimeoutRef.current) clearTimeout(typingTimeoutRef.current)
    }
  }, [token, chatUrl, roomId])

  const sendMessage = useCallback((message: string) => {
    if (!socketRef.current || !room) return
    socketRef.current.emit('send_message', { roomId: room.id, message })
  }, [room])

  const sendTypingStart = useCallback(() => {
    if (!socketRef.current || !room) return
    socketRef.current.emit('typing_start', { roomId: room.id })
  }, [room])

  const sendTypingStop = useCallback(() => {
    if (!socketRef.current || !room) return
    socketRef.current.emit('typing_stop', { roomId: room.id })
  }, [room])

  const markRead = useCallback(() => {
    if (!socketRef.current || !room) return
    socketRef.current.emit('mark_read', { roomId: room.id })
  }, [room])

  return { connected, room, messages, isTyping, sendMessage, sendTypingStart, sendTypingStop, markRead }
}
