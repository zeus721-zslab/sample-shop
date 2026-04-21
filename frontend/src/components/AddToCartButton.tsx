'use client'

import { cartApi, ApiError } from '@/lib/api'
import { useAuth } from '@/store/auth'
import { AnimatePresence, motion } from 'framer-motion'
import { useRouter } from 'next/navigation'
import { useState } from 'react'

interface Props {
  productId: number
  productName: string
  isSoldout: boolean
  maxStock: number
}

export default function AddToCartButton({ productId, isSoldout, maxStock }: Props) {
  const { token } = useAuth()
  const router = useRouter()
  const [quantity, setQuantity] = useState(1)
  const [status, setStatus] = useState<'idle' | 'loading' | 'done' | 'error'>('idle')
  const [buyStatus, setBuyStatus] = useState<'idle' | 'loading'>('idle')
  const [errorMsg, setErrorMsg] = useState('')

  async function handleAdd() {
    if (!token) {
      router.push('/login')
      return
    }
    setStatus('loading')
    setErrorMsg('')
    try {
      await cartApi.add(token, { product_id: productId, quantity })
      setStatus('done')
      setTimeout(() => setStatus('idle'), 2000)
    } catch (err) {
      setErrorMsg(err instanceof ApiError ? err.message : '오류가 발생했습니다.')
      setStatus('error')
      setTimeout(() => setStatus('idle'), 3000)
    }
  }

  async function handleBuyNow() {
    if (!token) {
      router.push('/login')
      return
    }
    setBuyStatus('loading')
    setErrorMsg('')
    try {
      await cartApi.add(token, { product_id: productId, quantity })
      router.push('/checkout')
    } catch (err) {
      setErrorMsg(err instanceof ApiError ? err.message : '오류가 발생했습니다.')
      setBuyStatus('idle')
    }
  }

  if (isSoldout) {
    return (
      <button disabled className="w-full py-4 bg-gray-100 text-gray-400 rounded-lg font-medium cursor-not-allowed">
        품절
      </button>
    )
  }

  return (
    <div className="space-y-3">
      {/* 수량 */}
      <div className="flex items-center gap-4">
        <span className="text-sm text-gray-500">수량</span>
        <div className="flex items-center border border-gray-200 rounded-lg">
          <button
            onClick={() => setQuantity((q) => Math.max(1, q - 1))}
            className="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-l-lg"
          >
            −
          </button>
          <motion.span
            key={quantity}
            initial={{ scale: 1.3, opacity: 0.6 }}
            animate={{ scale: 1, opacity: 1 }}
            transition={{ duration: 0.18, ease: 'easeOut' }}
            className="w-12 text-center text-sm font-medium"
          >
            {quantity}
          </motion.span>
          <button
            onClick={() => setQuantity((q) => Math.min(maxStock, q + 1))}
            className="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-r-lg"
          >
            +
          </button>
        </div>
      </div>

      {errorMsg && <p className="text-xs text-red-500">{errorMsg}</p>}

      <div className="flex gap-2 relative">
        {/* 장바구니 성공 플로팅 뱃지 */}
        <AnimatePresence>
          {status === 'done' && (
            <motion.span
              key="badge"
              initial={{ opacity: 0, y: 0, scale: 0.8 }}
              animate={{ opacity: 1, y: -36, scale: 1 }}
              exit={{ opacity: 0, y: -52, scale: 0.7 }}
              transition={{ duration: 0.4, ease: 'easeOut' }}
              className="absolute left-1/4 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-full pointer-events-none z-10"
            >
              +{quantity} 담겼습니다
            </motion.span>
          )}
        </AnimatePresence>

        <motion.button
          onClick={handleAdd}
          disabled={status === 'loading'}
          whileHover={{ scale: status === 'loading' ? 1 : 1.01 }}
          whileTap={{ scale: 0.98 }}
          transition={{ duration: 0.15 }}
          className={`flex-1 py-4 rounded-lg font-medium text-sm transition-colors ${
            status === 'done'
              ? 'bg-green-600 text-white'
              : 'bg-gray-900 text-white hover:bg-gray-700 disabled:opacity-50'
          }`}
        >
          {status === 'loading' ? '담는 중...' : status === 'done' ? '담겼습니다 ✓' : '장바구니 담기'}
        </motion.button>

        <motion.button
          onClick={handleBuyNow}
          disabled={buyStatus === 'loading'}
          whileHover={{ scale: 1.01 }}
          whileTap={{ scale: 0.98 }}
          transition={{ duration: 0.15 }}
          className="px-4 py-4 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50"
        >
          {buyStatus === 'loading' ? '처리 중…' : '바로구매'}
        </motion.button>
      </div>
    </div>
  )
}
