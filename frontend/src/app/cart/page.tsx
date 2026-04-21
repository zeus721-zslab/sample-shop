'use client'

import { cartApi, ApiError } from '@/lib/api'
import { formatPrice } from '@/lib/format'
import { useAuth } from '@/store/auth'
import type { CartData } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useEffect, useState } from 'react'

export default function CartPage() {
  const { token, user, isLoaded } = useAuth()
  const router = useRouter()

  const [cart, setCart] = useState<CartData | null>(null)
  const [loading, setLoading] = useState(true)
  const [updatingId, setUpdatingId] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!isLoaded) return       // localStorage 복원 대기
    if (!token) {
      router.push('/login')
      return
    }
    loadCart()
  }, [isLoaded, token])

  async function loadCart() {
    if (!token) return
    try {
      const data = await cartApi.get(token)
      setCart(data)
    } catch {
      setError('장바구니를 불러오지 못했습니다.')
    } finally {
      setLoading(false)
    }
  }

  async function handleQuantityChange(cartItemId: string, quantity: number) {
    if (!token) return
    setUpdatingId(cartItemId)
    try {
      if (quantity === 0) {
        await cartApi.remove(token, cartItemId)
      } else {
        await cartApi.update(token, cartItemId, quantity)
      }
      await loadCart()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : '오류가 발생했습니다.')
    } finally {
      setUpdatingId(null)
    }
  }

  async function handleRemove(cartItemId: string) {
    if (!token) return
    setUpdatingId(cartItemId)
    try {
      await cartApi.remove(token, cartItemId)
      await loadCart()
    } catch {
      setError('삭제 중 오류가 발생했습니다.')
    } finally {
      setUpdatingId(null)
    }
  }

  if (!user) return null

  if (loading) {
    return (
      <div className="mx-auto max-w-screen-lg px-4 py-10">
        <div className="space-y-4">
          {[1, 2, 3].map((i) => (
            <div key={i} className="flex gap-4 animate-pulse">
              <div className="w-24 h-24 bg-gray-100 rounded" />
              <div className="flex-1 space-y-2 py-1">
                <div className="h-4 bg-gray-100 rounded w-2/3" />
                <div className="h-4 bg-gray-100 rounded w-1/3" />
              </div>
            </div>
          ))}
        </div>
      </div>
    )
  }

  if (!cart || cart.items.length === 0) {
    return (
      <div className="mx-auto max-w-screen-lg px-4 py-24 text-center">
        <p className="text-lg text-gray-400 mb-6">장바구니가 비어 있습니다.</p>
        <Link
          href="/products"
          className="inline-block bg-gray-900 text-white px-8 py-3 rounded-full hover:bg-gray-700 transition-colors"
        >
          쇼핑 계속하기
        </Link>
      </div>
    )
  }

  const SHIPPING_FREE_THRESHOLD = 50_000
  const SHIPPING_FEE = 3_000
  const shippingFee = cart.subtotal >= SHIPPING_FREE_THRESHOLD ? 0 : SHIPPING_FEE
  const totalAmount = cart.subtotal + shippingFee

  return (
    <div className="mx-auto max-w-screen-lg px-4 py-10">
      <h1 className="text-2xl font-bold mb-8">장바구니 ({cart.count})</h1>

      {error && (
        <p className="mb-4 text-sm text-red-500 bg-red-50 rounded-lg px-4 py-2">{error}</p>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* 상품 목록 */}
        <div className="lg:col-span-2 space-y-4">
          {cart.items.map((item) => (
            <div key={item.cart_item_id} className="flex gap-4 py-4 border-b border-gray-100">
              {/* 이미지 */}
              <Link href={`/products/${item.slug}`} className="flex-shrink-0">
                <div className="relative w-24 h-24 bg-gray-50 rounded overflow-hidden">
                  <Image
                    src={item.image ?? '/placeholder.png'}
                    alt={item.name}
                    fill
                    className="object-cover"
                    sizes="96px"
                  />
                  {item.is_soldout && (
                    <div className="absolute inset-0 bg-black/40 flex items-center justify-center">
                      <span className="text-white text-[10px] font-medium">품절</span>
                    </div>
                  )}
                </div>
              </Link>

              {/* 정보 */}
              <div className="flex-1 min-w-0">
                <Link href={`/products/${item.slug}`} className="text-sm font-medium hover:underline line-clamp-2">
                  {item.name}
                </Link>
                <p className="text-sm font-bold mt-1">{formatPrice(item.effective_price)}</p>

                {/* 수량 조절 */}
                <div className="flex items-center gap-2 mt-2">
                  <div className="flex items-center border border-gray-200 rounded">
                    <button
                      onClick={() => handleQuantityChange(item.cart_item_id, item.quantity - 1)}
                      disabled={!!updatingId || item.quantity <= 1}
                      className="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-50 disabled:opacity-30"
                    >
                      −
                    </button>
                    <span className="w-8 text-center text-sm">{item.quantity}</span>
                    <button
                      onClick={() => handleQuantityChange(item.cart_item_id, item.quantity + 1)}
                      disabled={!!updatingId || item.quantity >= item.stock}
                      className="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-50 disabled:opacity-30"
                    >
                      +
                    </button>
                  </div>
                  <button
                    onClick={() => handleRemove(item.cart_item_id)}
                    disabled={!!updatingId}
                    className="text-xs text-gray-400 hover:text-gray-700 ml-2"
                  >
                    삭제
                  </button>
                </div>
              </div>

              {/* 소계 */}
              <div className="text-right flex-shrink-0">
                <p className="font-bold">{formatPrice(item.line_total)}</p>
              </div>
            </div>
          ))}
        </div>

        {/* 결제 요약 */}
        <div className="lg:col-span-1">
          <div className="border border-gray-100 rounded-xl p-6 sticky top-24 space-y-3">
            <h2 className="font-semibold text-lg mb-4">주문 요약</h2>
            <div className="flex justify-between text-sm">
              <span className="text-gray-500">상품 금액</span>
              <span>{formatPrice(cart.subtotal)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-500">배송비</span>
              {shippingFee === 0 ? (
                <span className="text-blue-500">무료</span>
              ) : (
                <span>{formatPrice(shippingFee)}</span>
              )}
            </div>
            {shippingFee > 0 && (
              <p className="text-xs text-gray-400">
                {formatPrice(SHIPPING_FREE_THRESHOLD - cart.subtotal)} 더 담으면 무료배송
              </p>
            )}
            <div className="border-t pt-3 flex justify-between font-bold text-lg">
              <span>총 결제금액</span>
              <span className="text-red-500">{formatPrice(totalAmount)}</span>
            </div>
            <Link
              href="/checkout"
              className="block w-full bg-gray-900 text-white text-center py-4 rounded-lg font-medium hover:bg-gray-700 transition-colors mt-2"
            >
              주문하기
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}
