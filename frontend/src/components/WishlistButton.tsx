'use client'

import { useWishlist } from '@/hooks/useWishlist'
import { useRouter } from 'next/navigation'

interface Props {
  productId: number
  className?: string
}

export default function WishlistButton({ productId, className = '' }: Props) {
  const { wishlisted, toggle, loading, isLoggedIn } = useWishlist(productId)
  const router = useRouter()

  async function handleClick() {
    if (!isLoggedIn) {
      router.push('/login')
      return
    }
    await toggle()
  }

  return (
    <button
      onClick={handleClick}
      disabled={loading}
      aria-label={wishlisted ? '위시리스트에서 제거' : '위시리스트에 추가'}
      className={`p-2.5 rounded-full border transition-all ${
        wishlisted
          ? 'bg-red-50 border-red-200 text-red-500'
          : 'bg-white border-gray-200 text-gray-400 hover:border-gray-900 hover:text-gray-900'
      } disabled:opacity-50 ${className}`}
    >
      <svg className="w-5 h-5" fill={wishlisted ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
        <path
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth={1.5}
          d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
        />
      </svg>
    </button>
  )
}
