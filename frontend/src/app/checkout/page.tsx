'use client'

import { cartApi, orderApi, ApiError } from '@/lib/api'
import { formatPrice } from '@/lib/format'
import { useAuth } from '@/store/auth'
import type { CartData } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useEffect, useState } from 'react'

interface ShippingForm {
  recipient: string
  phone: string
  postal_code: string
  address: string
  detail: string
}

const SHIPPING_FREE_THRESHOLD = 50_000
const SHIPPING_FEE = 3_000

export default function CheckoutPage() {
  const { token, user, isLoaded } = useAuth()
  const router = useRouter()

  const [cart, setCart] = useState<CartData | null>(null)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [couponCode, setCouponCode] = useState('')

  const [form, setForm] = useState<ShippingForm>({
    recipient: '',
    phone: '',
    postal_code: '',
    address: '',
    detail: '',
  })

  useEffect(() => {
    if (!isLoaded) return       // localStorage 복원 대기
    if (!token) {
      router.push('/login?redirect=/checkout')
      return
    }
    loadCart()
  }, [isLoaded, token])

  // 로그인 유저 이름 기본값 채우기
  useEffect(() => {
    if (user && !form.recipient) {
      setForm((f) => ({ ...f, recipient: user.name }))
    }
  }, [user])

  async function loadCart() {
    if (!token) return
    try {
      const data = await cartApi.get(token)
      if (data.items.length === 0) {
        router.push('/cart')
        return
      }
      setCart(data)
    } catch {
      setError('장바구니를 불러오지 못했습니다.')
    } finally {
      setLoading(false)
    }
  }

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    const { name, value } = e.target
    setForm((f) => ({ ...f, [name]: value }))
  }

  function validate(): string | null {
    if (!form.recipient.trim()) return '수취인 이름을 입력해 주세요.'
    if (!form.phone.trim()) return '연락처를 입력해 주세요.'
    if (!form.postal_code.trim()) return '우편번호를 입력해 주세요.'
    if (!form.address.trim()) return '주소를 입력해 주세요.'
    return null
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    const validationError = validate()
    if (validationError) {
      setError(validationError)
      return
    }

    setSubmitting(true)
    setError(null)

    try {
      const { order } = await orderApi.create(token!, {
        shipping_address: {
          recipient: form.recipient.trim(),
          phone: form.phone.trim(),
          postal_code: form.postal_code.trim(),
          address: form.address.trim(),
          detail: form.detail.trim() || undefined,
        },
        coupon_code: couponCode.trim() || undefined,
      })
      router.push(`/order/complete?id=${order.id}`)
    } catch (err) {
      setError(err instanceof ApiError ? err.message : '주문 처리 중 오류가 발생했습니다.')
      setSubmitting(false)
    }
  }

  if (!user) return null

  if (loading) {
    return (
      <div className="mx-auto max-w-screen-lg px-4 py-10">
        <div className="animate-pulse space-y-6">
          <div className="h-8 bg-gray-100 rounded w-40" />
          <div className="h-64 bg-gray-100 rounded" />
        </div>
      </div>
    )
  }

  if (!cart) return null

  const shippingFee = cart.subtotal >= SHIPPING_FREE_THRESHOLD ? 0 : SHIPPING_FEE
  const totalAmount = cart.subtotal + shippingFee

  return (
    <div className="mx-auto max-w-screen-lg px-4 py-10">
      <h1 className="text-2xl font-bold mb-8">주문 / 결제</h1>

      <form onSubmit={handleSubmit}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* 왼쪽: 배송지 + 쿠폰 */}
          <div className="lg:col-span-2 space-y-8">
            {/* 배송지 */}
            <section>
              <h2 className="text-base font-semibold mb-4 pb-2 border-b border-gray-100">배송지 정보</h2>
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs text-gray-500 mb-1">수취인 <span className="text-red-400">*</span></label>
                    <input
                      type="text"
                      name="recipient"
                      value={form.recipient}
                      onChange={handleChange}
                      placeholder="홍길동"
                      className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                    />
                  </div>
                  <div>
                    <label className="block text-xs text-gray-500 mb-1">연락처 <span className="text-red-400">*</span></label>
                    <input
                      type="tel"
                      name="phone"
                      value={form.phone}
                      onChange={handleChange}
                      placeholder="010-0000-0000"
                      className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs text-gray-500 mb-1">우편번호 <span className="text-red-400">*</span></label>
                  <input
                    type="text"
                    name="postal_code"
                    value={form.postal_code}
                    onChange={handleChange}
                    placeholder="12345"
                    maxLength={6}
                    className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                  />
                </div>
                <div>
                  <label className="block text-xs text-gray-500 mb-1">주소 <span className="text-red-400">*</span></label>
                  <input
                    type="text"
                    name="address"
                    value={form.address}
                    onChange={handleChange}
                    placeholder="서울특별시 강남구 테헤란로 123"
                    className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                  />
                </div>
                <div>
                  <label className="block text-xs text-gray-500 mb-1">상세주소</label>
                  <input
                    type="text"
                    name="detail"
                    value={form.detail}
                    onChange={handleChange}
                    placeholder="동/호수, 건물명 등"
                    className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                  />
                </div>
              </div>
            </section>

            {/* 쿠폰 */}
            <section>
              <h2 className="text-base font-semibold mb-4 pb-2 border-b border-gray-100">쿠폰 / 할인코드</h2>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={couponCode}
                  onChange={(e) => setCouponCode(e.target.value)}
                  placeholder="쿠폰 코드를 입력해 주세요"
                  className="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
                />
                <button
                  type="button"
                  className="px-4 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                >
                  적용
                </button>
              </div>
            </section>

            {/* 주문 상품 */}
            <section>
              <h2 className="text-base font-semibold mb-4 pb-2 border-b border-gray-100">
                주문 상품 ({cart.count})
              </h2>
              <div className="space-y-4">
                {cart.items.map((item) => (
                  <div key={item.cart_item_id} className="flex gap-4">
                    <div className="relative w-16 h-16 bg-gray-50 rounded overflow-hidden flex-shrink-0">
                      <Image
                        src={item.image ?? '/placeholder.png'}
                        alt={item.name}
                        fill
                        className="object-cover"
                        sizes="64px"
                      />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium line-clamp-1">{item.name}</p>
                      <p className="text-xs text-gray-400 mt-0.5">수량 {item.quantity}</p>
                    </div>
                    <p className="text-sm font-bold flex-shrink-0">{formatPrice(item.line_total)}</p>
                  </div>
                ))}
              </div>
            </section>
          </div>

          {/* 오른쪽: 결제 요약 */}
          <div className="lg:col-span-1">
            <div className="border border-gray-100 rounded-xl p-6 sticky top-24 space-y-3">
              <h2 className="font-semibold text-lg mb-4">결제 요약</h2>
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

              {error && (
                <p className="text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">{error}</p>
              )}

              <button
                type="submit"
                disabled={submitting}
                className="w-full bg-gray-900 text-white py-4 rounded-lg font-medium hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed mt-2"
              >
                {submitting ? '처리 중…' : `${formatPrice(totalAmount)} 결제하기`}
              </button>

              <p className="text-xs text-gray-400 text-center">
                결제 시 이용약관에 동의한 것으로 간주합니다
              </p>

              <Link
                href="/cart"
                className="block text-center text-xs text-gray-400 hover:text-gray-700 underline"
              >
                장바구니로 돌아가기
              </Link>
            </div>
          </div>
        </div>
      </form>
    </div>
  )
}
