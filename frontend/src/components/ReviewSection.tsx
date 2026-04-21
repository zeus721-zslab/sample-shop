'use client'

import { reviewApi, ApiError } from '@/lib/api'
import { useAuth } from '@/store/auth'
import type { Review } from '@/types'
import { useEffect, useState } from 'react'

interface Props {
  productId: number
  initialReviews: Review[]
  initialTotal: number
}

function StarRating({ value, onChange }: { value: number; onChange?: (v: number) => void }) {
  return (
    <div className="flex gap-0.5">
      {[1, 2, 3, 4, 5].map((s) => (
        <button
          key={s}
          type={onChange ? 'button' : undefined}
          onClick={() => onChange?.(s)}
          className={`text-xl ${s <= value ? 'text-amber-400' : 'text-gray-200'} ${onChange ? 'hover:text-amber-300 cursor-pointer' : 'cursor-default'}`}
        >
          ★
        </button>
      ))}
    </div>
  )
}

export default function ReviewSection({ productId, initialReviews, initialTotal }: Props) {
  const { token, user } = useAuth()
  const [reviews, setReviews] = useState<Review[]>(initialReviews)
  const [total, setTotal] = useState(initialTotal)

  // 작성 폼 상태
  const [showForm, setShowForm] = useState(false)
  const [rating, setRating] = useState(5)
  const [content, setContent] = useState('')
  const [title, setTitle] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [formError, setFormError] = useState<string | null>(null)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!token) return
    setSubmitting(true)
    setFormError(null)

    try {
      const review = await reviewApi.create(token, productId, { rating, title: title || undefined, content })
      setReviews((prev) => [review, ...prev])
      setTotal((t) => t + 1)
      setShowForm(false)
      setContent('')
      setTitle('')
      setRating(5)
    } catch (err) {
      setFormError(err instanceof ApiError ? err.message : '오류가 발생했습니다.')
    } finally {
      setSubmitting(false)
    }
  }

  async function handleDelete(reviewId: number) {
    if (!token || !confirm('리뷰를 삭제하시겠습니까?')) return
    try {
      await reviewApi.delete(token, reviewId)
      setReviews((prev) => prev.filter((r) => r.id !== reviewId))
      setTotal((t) => t - 1)
    } catch {}
  }

  const avgRating = reviews.length > 0
    ? (reviews.reduce((s, r) => s + r.rating, 0) / reviews.length).toFixed(1)
    : null

  return (
    <section className="border-t pt-10 mt-10">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-lg font-bold">리뷰 {total > 0 ? `(${total})` : ''}</h2>
          {avgRating && (
            <div className="flex items-center gap-2 mt-1">
              <span className="text-amber-400 text-lg">★</span>
              <span className="font-bold">{avgRating}</span>
              <span className="text-sm text-gray-400">/ 5.0</span>
            </div>
          )}
        </div>

        {user && !showForm && (
          <button
            onClick={() => setShowForm(true)}
            className="text-sm border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
          >
            리뷰 작성
          </button>
        )}
      </div>

      {/* 작성 폼 */}
      {showForm && (
        <form onSubmit={handleSubmit} className="mb-8 p-5 bg-gray-50 rounded-xl space-y-4">
          <div>
            <label className="text-sm font-medium text-gray-700 mb-2 block">별점</label>
            <StarRating value={rating} onChange={setRating} />
          </div>
          <div>
            <label className="text-sm font-medium text-gray-700 mb-1 block">제목 (선택)</label>
            <input
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              maxLength={100}
              placeholder="리뷰 제목"
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
            />
          </div>
          <div>
            <label className="text-sm font-medium text-gray-700 mb-1 block">내용 <span className="text-red-500">*</span></label>
            <textarea
              required
              minLength={10}
              maxLength={2000}
              rows={4}
              value={content}
              onChange={(e) => setContent(e.target.value)}
              placeholder="상품에 대한 솔직한 리뷰를 남겨주세요. (최소 10자)"
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"
            />
            <p className="text-xs text-gray-400 text-right mt-1">{content.length}/2000</p>
          </div>

          {formError && <p className="text-sm text-red-500">{formError}</p>}

          <div className="flex gap-2">
            <button
              type="submit"
              disabled={submitting}
              className="flex-1 bg-gray-900 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50 transition-colors"
            >
              {submitting ? '등록 중...' : '리뷰 등록'}
            </button>
            <button
              type="button"
              onClick={() => setShowForm(false)}
              className="px-4 py-2.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors"
            >
              취소
            </button>
          </div>
        </form>
      )}

      {/* 리뷰 목록 */}
      {reviews.length === 0 ? (
        <p className="text-sm text-gray-400 py-8 text-center">아직 작성된 리뷰가 없습니다.</p>
      ) : (
        <div className="space-y-6">
          {reviews.map((review) => (
            <div key={review.id} className="pb-6 border-b border-gray-50 last:border-0">
              <div className="flex items-start justify-between gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-1">
                    <StarRating value={review.rating} />
                    {review.is_verified && (
                      <span className="text-[10px] bg-green-50 text-green-600 px-1.5 py-0.5 rounded font-medium">
                        구매확인
                      </span>
                    )}
                  </div>
                  {review.title && (
                    <p className="text-sm font-semibold text-gray-900 mb-1">{review.title}</p>
                  )}
                  <p className="text-sm text-gray-600 leading-relaxed">{review.content}</p>
                  <div className="flex items-center gap-2 mt-2 text-xs text-gray-400">
                    <span>{review.user?.name ?? '익명'}</span>
                    <span>·</span>
                    <span>{new Date(review.created_at).toLocaleDateString('ko-KR')}</span>
                  </div>
                </div>
                {user?.id === review.user_id && (
                  <button
                    onClick={() => handleDelete(review.id)}
                    className="text-xs text-gray-400 hover:text-red-500 transition-colors flex-shrink-0"
                  >
                    삭제
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </section>
  )
}
