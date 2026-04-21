'use client'

import { cartApi, ApiError } from '@/lib/api'
import { useAuth } from '@/store/auth'
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
          <span className="w-12 text-center text-sm font-medium">{quantity}</span>
          <button
            onClick={() => setQuantity((q) => Math.min(maxStock, q + 1))}
            className="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-r-lg"
          >
            +
          </button>
        </div>
      </div>

      {errorMsg && <p className="text-xs text-red-500">{errorMsg}</p>}

      <div className="flex gap-2">
        <button
          onClick={handleAdd}
          disabled={status === 'loading'}
          className={`flex-1 py-4 rounded-lg font-medium text-sm transition-colors ${
            status === 'done'
              ? 'bg-green-600 text-white'
              : 'bg-gray-900 text-white hover:bg-gray-700 disabled:opacity-50'
          }`}
        >
          {status === 'loading' ? '담는 중...' : status === 'done' ? '담겼습니다 ✓' : '장바구니 담기'}
        </button>
        <button
          onClick={handleBuyNow}
          disabled={buyStatus === 'loading'}
          className="px-4 py-4 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50"
        >
          {buyStatus === 'loading' ? '처리 중…' : '바로구매'}
        </button>
      </div>
    </div>
  )
}
