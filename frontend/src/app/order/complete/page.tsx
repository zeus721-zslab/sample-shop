'use client'

import { orderApi } from '@/lib/api'
import { formatPrice } from '@/lib/format'
import { useAuth } from '@/store/auth'
import type { Order } from '@/types'
import Link from 'next/link'
import { useRouter, useSearchParams } from 'next/navigation'
import { useEffect, useState, Suspense } from 'react'

function OrderCompleteInner() {
  const { token, user, isLoaded } = useAuth()
  const router = useRouter()
  const searchParams = useSearchParams()
  const orderId = Number(searchParams.get('id'))

  const [order, setOrder] = useState<Order | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!isLoaded) return
    if (!token) {
      router.push('/login')
      return
    }
    if (!orderId) {
      router.push('/')
      return
    }
    orderApi.get(token, orderId).then(setOrder).finally(() => setLoading(false))
  }, [isLoaded, token, orderId])

  if (!user) return null

  if (loading) {
    return (
      <div className="mx-auto max-w-screen-sm px-4 py-16 text-center">
        <div className="animate-pulse space-y-4">
          <div className="h-16 w-16 bg-gray-100 rounded-full mx-auto" />
          <div className="h-6 bg-gray-100 rounded w-48 mx-auto" />
        </div>
      </div>
    )
  }

  if (!order) {
    return (
      <div className="mx-auto max-w-screen-sm px-4 py-16 text-center">
        <p className="text-gray-400 mb-4">주문 정보를 불러올 수 없습니다.</p>
        <Link href="/" className="underline text-sm">홈으로</Link>
      </div>
    )
  }

  const STATUS_LABEL: Record<string, string> = {
    pending: '결제 대기',
    paid: '결제 완료',
    shipping: '배송 중',
    delivered: '배송 완료',
    cancelled: '취소됨',
  }

  return (
    <div className="mx-auto max-w-screen-sm px-4 py-16">
      {/* 완료 헤더 */}
      <div className="text-center mb-12">
        <div className="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 className="text-2xl font-bold mb-2">주문이 완료되었습니다</h1>
        <p className="text-gray-400 text-sm">주문번호: {order.order_number}</p>
      </div>

      {/* 주문 상세 카드 */}
      <div className="border border-gray-100 rounded-xl p-6 space-y-6">
        {/* 상품 목록 */}
        <div>
          <h2 className="text-sm font-semibold text-gray-700 mb-3">주문 상품</h2>
          <div className="space-y-3">
            {order.items.map((item) => (
              <div key={item.id} className="flex justify-between items-start text-sm">
                <div>
                  <p className="font-medium">{item.product_name}</p>
                  <p className="text-gray-400 text-xs mt-0.5">수량 {item.quantity}</p>
                </div>
                <p className="font-bold">{formatPrice(item.total_price)}</p>
              </div>
            ))}
          </div>
        </div>

        {/* 금액 */}
        <div className="border-t pt-4 space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-gray-500">상품 금액</span>
            <span>{formatPrice(order.total_amount)}</span>
          </div>
          {order.discount_amount > 0 && (
            <div className="flex justify-between text-sm">
              <span className="text-gray-500">
                쿠폰 할인
                {order.coupon_code && (
                  <code className="ml-1.5 bg-green-50 text-green-700 px-1.5 py-0.5 rounded text-[11px]">
                    {order.coupon_code}
                  </code>
                )}
              </span>
              <span className="text-green-600">-{formatPrice(order.discount_amount)}</span>
            </div>
          )}
          <div className="flex justify-between font-bold text-base pt-1 border-t">
            <span>최종 결제금액</span>
            <span className="text-red-500">{formatPrice(order.final_amount)}</span>
          </div>
        </div>

        {/* 배송지 */}
        <div className="border-t pt-4">
          <h2 className="text-sm font-semibold text-gray-700 mb-2">배송지</h2>
          <div className="text-sm text-gray-600 space-y-1">
            <p>{order.shipping_address.recipient} ({order.shipping_address.phone})</p>
            <p>[{order.shipping_address.postal_code}] {order.shipping_address.address}
              {order.shipping_address.detail ? ` ${order.shipping_address.detail}` : ''}
            </p>
          </div>
        </div>

        {/* 상태 */}
        <div className="border-t pt-4 flex justify-between items-center">
          <span className="text-sm text-gray-500">주문 상태</span>
          <span className="text-sm font-semibold bg-gray-900 text-white px-3 py-1 rounded-full">
            {STATUS_LABEL[order.status] ?? order.status}
          </span>
        </div>
      </div>

      {/* 액션 버튼 */}
      <div className="mt-6 flex flex-col gap-3">
        <Link
          href="/my?tab=orders"
          className="block w-full text-center bg-gray-900 text-white py-3.5 rounded-lg font-medium hover:bg-gray-700 transition-colors"
        >
          주문 내역 보기
        </Link>
        <Link
          href="/products"
          className="block w-full text-center border border-gray-200 py-3.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
        >
          쇼핑 계속하기
        </Link>
      </div>
    </div>
  )
}

export default function OrderCompletePage() {
  return (
    <Suspense fallback={
      <div className="mx-auto max-w-screen-sm px-4 py-16 text-center">
        <div className="animate-pulse space-y-4">
          <div className="h-16 w-16 bg-gray-100 rounded-full mx-auto" />
          <div className="h-6 bg-gray-100 rounded w-48 mx-auto" />
        </div>
      </div>
    }>
      <OrderCompleteInner />
    </Suspense>
  )
}
