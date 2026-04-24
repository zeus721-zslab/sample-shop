'use client'

import { myApi, wishlistApi, reviewApi, ApiError } from '@/lib/api'
import { formatPrice } from '@/lib/format'
import { useAuth } from '@/store/auth'
import type { Order, Review, WishlistItem } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import { useRouter, useSearchParams } from 'next/navigation'
import { useEffect, useState, Suspense } from 'react'

type Tab = 'profile' | 'orders' | 'wishlist' | 'reviews'

const TABS: { key: Tab; label: string }[] = [
  { key: 'profile', label: '프로필' },
  { key: 'orders', label: '주문 내역' },
  { key: 'wishlist', label: '위시리스트' },
  { key: 'reviews', label: '내 리뷰' },
]

const STATUS_LABEL: Record<string, string> = {
  pending: '결제 대기',
  paid: '결제 완료',
  shipping: '배송 중',
  delivered: '배송 완료',
  cancelled: '취소됨',
}

// ── Profile Tab ──────────────────────────────────────────────────────────────

function ProfileTab() {
  const { token, user } = useAuth()
  const [name, setName] = useState(user?.name ?? '')
  const [phone, setPhone] = useState(user?.phone ?? '')
  const [currentPw, setCurrentPw] = useState('')
  const [newPw, setNewPw] = useState('')
  const [newPwConfirm, setNewPwConfirm] = useState('')
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState<{ type: 'ok' | 'err'; text: string } | null>(null)

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    if (!token) return

    if (newPw && newPw !== newPwConfirm) {
      setMessage({ type: 'err', text: '새 비밀번호가 일치하지 않습니다.' })
      return
    }

    setSaving(true)
    setMessage(null)

    try {
      await myApi.updateProfile(token, {
        name: name.trim(),
        phone: phone.trim() || undefined,
        ...(newPw ? { current_password: currentPw, password: newPw, password_confirmation: newPwConfirm } : {}),
      })
      setMessage({ type: 'ok', text: '프로필이 저장되었습니다.' })
      setCurrentPw('')
      setNewPw('')
      setNewPwConfirm('')
    } catch (err) {
      setMessage({ type: 'err', text: err instanceof ApiError ? err.message : '저장 중 오류가 발생했습니다.' })
    } finally {
      setSaving(false)
    }
  }

  return (
    <form onSubmit={handleSave} className="max-w-md space-y-5">
      <div>
        <label className="block text-xs text-gray-500 mb-1">이메일</label>
        <input
          type="email"
          value={user?.email ?? ''}
          disabled
          className="w-full border border-gray-100 rounded-lg px-4 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed"
        />
      </div>
      <div>
        <label className="block text-xs text-gray-500 mb-1">이름</label>
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
        />
      </div>
      <div>
        <label className="block text-xs text-gray-500 mb-1">연락처</label>
        <input
          type="tel"
          value={phone}
          onChange={(e) => setPhone(e.target.value)}
          placeholder="010-0000-0000"
          className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
        />
      </div>

      <div className="border-t pt-5">
        <p className="text-xs text-gray-400 mb-3">비밀번호 변경 (변경 시에만 입력)</p>
        <div className="space-y-3">
          <input
            type="password"
            value={currentPw}
            onChange={(e) => setCurrentPw(e.target.value)}
            placeholder="현재 비밀번호"
            className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
          />
          <input
            type="password"
            value={newPw}
            onChange={(e) => setNewPw(e.target.value)}
            placeholder="새 비밀번호"
            className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
          />
          <input
            type="password"
            value={newPwConfirm}
            onChange={(e) => setNewPwConfirm(e.target.value)}
            placeholder="새 비밀번호 확인"
            className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-gray-900"
          />
        </div>
      </div>

      {message && (
        <p className={`text-sm rounded-lg px-4 py-2 ${message.type === 'ok' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'}`}>
          {message.text}
        </p>
      )}

      <button
        type="submit"
        disabled={saving}
        className="w-full bg-gray-900 text-white py-3 rounded-lg font-medium hover:bg-gray-700 transition-colors disabled:opacity-50"
      >
        {saving ? '저장 중…' : '저장'}
      </button>
    </form>
  )
}

// ── Orders Tab ────────────────────────────────────────────────────────────────

function OrdersTab() {
  const { token } = useAuth()
  const [orders, setOrders] = useState<Order[]>([])
  const [loading, setLoading] = useState(true)
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [cancellingId, setCancellingId] = useState<number | null>(null)

  useEffect(() => {
    if (!token) return
    myApi.orders(token, page).then((res) => {
      setOrders(res.data)
      setLastPage(res.last_page)
    }).finally(() => setLoading(false))
  }, [token, page])

  async function handleCancel(id: number) {
    if (!token) return
    setCancellingId(id)
    try {
      const updated = await import('@/lib/api').then(m => m.orderApi.cancel(token, id))
      setOrders((prev) => prev.map((o) => (o.id === id ? updated : o)))
    } catch {
      // ignore
    } finally {
      setCancellingId(null)
    }
  }

  if (loading) {
    return (
      <div className="space-y-4">
        {[1, 2].map((i) => (
          <div key={i} className="animate-pulse border border-gray-100 rounded-xl p-6">
            <div className="h-4 bg-gray-100 rounded w-1/3 mb-3" />
            <div className="h-4 bg-gray-100 rounded w-1/2" />
          </div>
        ))}
      </div>
    )
  }

  if (orders.length === 0) {
    return (
      <div className="py-16 text-center">
        <p className="text-gray-400 mb-4">주문 내역이 없습니다.</p>
        <Link href="/products" className="inline-block bg-gray-900 text-white px-6 py-2.5 rounded-full text-sm hover:bg-gray-700">
          쇼핑하러 가기
        </Link>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      {orders.map((order) => (
        <div key={order.id} className="border border-gray-100 rounded-xl p-5">
          <div className="flex items-center justify-between mb-4">
            <div>
              <p className="text-xs text-gray-400">{new Date(order.created_at).toLocaleDateString('ko-KR')}</p>
              <p className="text-sm font-medium mt-0.5">{order.order_number}</p>
            </div>
            <span className={`text-xs font-medium px-3 py-1 rounded-full ${
              order.status === 'cancelled' ? 'bg-red-50 text-red-400' :
              order.status === 'delivered' ? 'bg-green-50 text-green-600' :
              'bg-gray-900 text-white'
            }`}>
              {STATUS_LABEL[order.status] ?? order.status}
            </span>
          </div>

          <div className="space-y-2 mb-4">
            {order.items.map((item) => (
              <div key={item.id} className="flex justify-between text-sm">
                <span className="text-gray-700 line-clamp-1">{item.product_name} × {item.quantity}</span>
                <span className="font-medium ml-4 flex-shrink-0">{formatPrice(item.total_price)}</span>
              </div>
            ))}
          </div>

          <div className="pt-3 border-t border-gray-50">
            {/* 금액 흐름 */}
            <div className="space-y-1 mb-2">
              {order.discount_amount > 0 && (
                <>
                  <div className="flex justify-between text-xs text-gray-400">
                    <span>상품 금액</span>
                    <span>{formatPrice(order.total_amount)}</span>
                  </div>
                  <div className="flex justify-between text-xs text-green-600">
                    <span>
                      쿠폰 할인
                      {order.coupon_code && (
                        <code className="ml-1 bg-green-50 px-1 rounded text-[10px]">{order.coupon_code}</code>
                      )}
                    </span>
                    <span>-{formatPrice(order.discount_amount)}</span>
                  </div>
                </>
              )}
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-bold">{formatPrice(order.final_amount)}</span>
              {(order.status === 'pending' || order.status === 'paid') && (
                <button
                  onClick={() => handleCancel(order.id)}
                  disabled={cancellingId === order.id}
                  className="text-xs text-gray-400 hover:text-red-400 underline disabled:opacity-50"
                >
                  {cancellingId === order.id ? '처리 중…' : '주문 취소'}
                </button>
              )}
            </div>
          </div>
        </div>
      ))}

      {/* 페이지네이션 */}
      {lastPage > 1 && (
        <div className="flex justify-center gap-2 pt-4">
          {Array.from({ length: lastPage }, (_, i) => i + 1).map((p) => (
            <button
              key={p}
              onClick={() => setPage(p)}
              className={`w-8 h-8 text-sm rounded-full ${p === page ? 'bg-gray-900 text-white' : 'border border-gray-200 hover:bg-gray-50'}`}
            >
              {p}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}

// ── Wishlist Tab ──────────────────────────────────────────────────────────────

function WishlistTab() {
  const { token } = useAuth()
  const [items, setItems] = useState<WishlistItem[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!token) return
    myApi.wishlist(token).then((res) => setItems(res.data)).finally(() => setLoading(false))
  }, [token])

  async function handleRemove(productId: number) {
    if (!token) return
    await wishlistApi.remove(token, productId)
    setItems((prev) => prev.filter((w) => w.product_id !== productId))
  }

  if (loading) {
    return (
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="animate-pulse">
            <div className="aspect-[3/4] bg-gray-100 rounded-lg mb-2" />
            <div className="h-3 bg-gray-100 rounded w-2/3" />
          </div>
        ))}
      </div>
    )
  }

  if (items.length === 0) {
    return (
      <div className="py-16 text-center">
        <p className="text-gray-400 mb-4">위시리스트가 비어 있습니다.</p>
        <Link href="/products" className="inline-block bg-gray-900 text-white px-6 py-2.5 rounded-full text-sm hover:bg-gray-700">
          상품 보러 가기
        </Link>
      </div>
    )
  }

  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 gap-4">
      {items.map((item) => {
        const product = item.product
        return (
          <div key={item.id} className="group">
            <Link href={product ? `/products/${product.slug}` : '#'}>
              <div className="relative aspect-[3/4] bg-gray-50 rounded-lg overflow-hidden mb-2">
                <Image
                  src={product?.images?.[0] ?? '/placeholder.png'}
                  alt={product?.name ?? ''}
                  fill
                  className="object-cover group-hover:scale-105 transition-transform duration-300"
                  sizes="(max-width: 640px) 50vw, 33vw"
                />
              </div>
              <p className="text-sm font-medium line-clamp-1">{product?.name}</p>
              <p className="text-sm text-gray-500">{product ? formatPrice(product.effective_price) : ''}</p>
            </Link>
            <button
              onClick={() => handleRemove(item.product_id)}
              className="text-xs text-gray-400 hover:text-red-400 mt-1 underline"
            >
              위시리스트에서 삭제
            </button>
          </div>
        )
      })}
    </div>
  )
}

// ── Reviews Tab ───────────────────────────────────────────────────────────────

function ReviewsTab() {
  const { token } = useAuth()
  const [reviews, setReviews] = useState<Review[]>([])
  const [loading, setLoading] = useState(true)
  const [deletingId, setDeletingId] = useState<number | null>(null)

  useEffect(() => {
    if (!token) return
    myApi.reviews(token).then((res) => setReviews(res.data)).finally(() => setLoading(false))
  }, [token])

  async function handleDelete(id: number) {
    if (!token) return
    setDeletingId(id)
    try {
      await reviewApi.delete(token, id)
      setReviews((prev) => prev.filter((r) => r.id !== id))
    } catch {
      // ignore
    } finally {
      setDeletingId(null)
    }
  }

  if (loading) {
    return (
      <div className="space-y-4">
        {[1, 2].map((i) => (
          <div key={i} className="animate-pulse border border-gray-100 rounded-xl p-5">
            <div className="h-4 bg-gray-100 rounded w-1/2 mb-2" />
            <div className="h-3 bg-gray-100 rounded w-full" />
          </div>
        ))}
      </div>
    )
  }

  if (reviews.length === 0) {
    return (
      <div className="py-16 text-center">
        <p className="text-gray-400">작성한 리뷰가 없습니다.</p>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      {reviews.map((review) => (
        <div key={review.id} className="border border-gray-100 rounded-xl p-5">
          <div className="flex items-start justify-between mb-2">
            <div>
              <div className="flex gap-0.5 mb-1">
                {[1, 2, 3, 4, 5].map((s) => (
                  <svg key={s} className={`w-3.5 h-3.5 ${s <= review.rating ? 'text-yellow-400' : 'text-gray-200'}`} fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                ))}
              </div>
              {review.title && <p className="text-sm font-semibold">{review.title}</p>}
            </div>
            <button
              onClick={() => handleDelete(review.id)}
              disabled={deletingId === review.id}
              className="text-xs text-gray-400 hover:text-red-400 underline disabled:opacity-50"
            >
              {deletingId === review.id ? '삭제 중…' : '삭제'}
            </button>
          </div>
          <p className="text-sm text-gray-600 leading-relaxed">{review.content}</p>
          <p className="text-xs text-gray-400 mt-2">{new Date(review.created_at).toLocaleDateString('ko-KR')}</p>
        </div>
      ))}
    </div>
  )
}

// ── Main ──────────────────────────────────────────────────────────────────────

function MyPageInner() {
  const { user, token, isLoaded } = useAuth()
  const router = useRouter()
  const searchParams = useSearchParams()
  const tabParam = (searchParams.get('tab') as Tab) || 'profile'
  const [activeTab, setActiveTab] = useState<Tab>(tabParam)

  useEffect(() => {
    if (!isLoaded) return
    if (!token) {
      router.push('/login?redirect=/my')
    }
  }, [isLoaded, token])

  useEffect(() => {
    setActiveTab(tabParam)
  }, [tabParam])

  function handleTabChange(tab: Tab) {
    setActiveTab(tab)
    const url = new URL(window.location.href)
    url.searchParams.set('tab', tab)
    window.history.pushState({}, '', url.toString())
  }

  if (!user) return null

  return (
    <div className="mx-auto max-w-screen-lg px-4 py-10">
      <div className="mb-8">
        <h1 className="text-2xl font-bold">{user.name}님</h1>
        <p className="text-sm text-gray-400 mt-1">{user.email}</p>
      </div>

      {/* 탭 */}
      <div className="flex gap-6 border-b border-gray-100 mb-8">
        {TABS.map((tab) => (
          <button
            key={tab.key}
            onClick={() => handleTabChange(tab.key)}
            className={`pb-3 text-sm font-medium border-b-2 transition-colors ${
              activeTab === tab.key
                ? 'border-gray-900 text-gray-900'
                : 'border-transparent text-gray-400 hover:text-gray-700'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* 탭 컨텐츠 */}
      {activeTab === 'profile' && <ProfileTab />}
      {activeTab === 'orders' && <OrdersTab />}
      {activeTab === 'wishlist' && <WishlistTab />}
      {activeTab === 'reviews' && <ReviewsTab />}
    </div>
  )
}

export default function MyPage() {
  return (
    <Suspense fallback={
      <div className="mx-auto max-w-screen-lg px-4 py-10">
        <div className="animate-pulse space-y-4">
          <div className="h-8 bg-gray-100 rounded w-32" />
          <div className="h-4 bg-gray-100 rounded w-48" />
        </div>
      </div>
    }>
      <MyPageInner />
    </Suspense>
  )
}
