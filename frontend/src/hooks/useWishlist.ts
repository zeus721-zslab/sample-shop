'use client'

import { wishlistApi } from '@/lib/api'
import { useAuth } from '@/store/auth'
import { useCallback, useEffect, useState } from 'react'

export function useWishlist(productId: number) {
  const { token } = useAuth()
  const [wishlisted, setWishlisted] = useState(false)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (!token) return
    wishlistApi.check(token, productId)
      .then((r) => setWishlisted(r.wishlisted))
      .catch(() => {})
  }, [token, productId])

  const toggle = useCallback(async () => {
    if (!token) return false
    setLoading(true)
    try {
      const r = await wishlistApi.toggle(token, productId)
      setWishlisted(r.wishlisted)
      return r.wishlisted
    } catch {
      return wishlisted
    } finally {
      setLoading(false)
    }
  }, [token, productId, wishlisted])

  return { wishlisted, toggle, loading, isLoggedIn: !!token }
}
