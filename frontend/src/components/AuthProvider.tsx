'use client'

import { AuthContext } from '@/store/auth'
import type { User } from '@/types'
import { useCallback, useEffect, useState } from 'react'

export default function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [token, setToken] = useState<string | null>(null)
  const [isLoaded, setIsLoaded] = useState(false)

  // localStorage에서 복원
  useEffect(() => {
    const storedToken = localStorage.getItem('zslab_token')
    const storedUser = localStorage.getItem('zslab_user')
    if (storedToken && storedUser) {
      try {
        setToken(storedToken)
        setUser(JSON.parse(storedUser))
      } catch {}
    }
    setIsLoaded(true)
  }, [])

  const setAuth = useCallback((u: User, t: string) => {
    setUser(u)
    setToken(t)
    localStorage.setItem('zslab_token', t)
    localStorage.setItem('zslab_user', JSON.stringify(u))
  }, [])

  const clearAuth = useCallback(() => {
    setUser(null)
    setToken(null)
    localStorage.removeItem('zslab_token')
    localStorage.removeItem('zslab_user')
  }, [])

  return (
    <AuthContext.Provider value={{ user, token, isLoaded, setAuth, clearAuth }}>
      {children}
    </AuthContext.Provider>
  )
}
