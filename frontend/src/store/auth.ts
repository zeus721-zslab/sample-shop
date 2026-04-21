'use client'

import type { User } from '@/types'
import { createContext, useContext } from 'react'

export interface AuthState {
  user: User | null
  token: string | null
  isLoaded: boolean   // localStorage 복원 완료 여부
  setAuth: (user: User, token: string) => void
  clearAuth: () => void
}

export const AuthContext = createContext<AuthState>({
  user: null,
  token: null,
  isLoaded: false,
  setAuth: () => {},
  clearAuth: () => {},
})

export function useAuth() {
  return useContext(AuthContext)
}
