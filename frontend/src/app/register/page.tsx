'use client'

import { authApi, ApiError } from '@/lib/api'
import { useAuth } from '@/store/auth'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { useState } from 'react'

export default function RegisterPage() {
  const router = useRouter()
  const { setAuth } = useAuth()

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    gender: '',
    birth_year: '',
  })
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [welcomeCoupon, setWelcomeCoupon] = useState<{ code: string; name: string } | null>(null)

  function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) {
    setForm((prev) => ({ ...prev, [e.target.name]: e.target.value }))
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(null)

    if (form.password !== form.password_confirmation) {
      setError('비밀번호가 일치하지 않습니다.')
      return
    }

    setLoading(true)
    try {
      const payload: Parameters<typeof authApi.register>[0] = {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
      }
      if (form.phone) (payload as Record<string, unknown>).phone = form.phone
      if (form.gender) payload.gender = form.gender
      if (form.birth_year) payload.birth_year = Number(form.birth_year)

      const data = await authApi.register(payload)
      setAuth(data.user, data.token)

      if (data.welcome_coupon) {
        setWelcomeCoupon(data.welcome_coupon)
        return // 쿠폰 팝업 표시 후 이동
      }

      router.push('/')
      router.refresh()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : '회원가입 중 오류가 발생했습니다.')
    } finally {
      setLoading(false)
    }
  }

  if (welcomeCoupon) {
    return (
      <div className="min-h-[70vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-sm text-center">
          <div className="text-5xl mb-4">🎁</div>
          <h2 className="text-xl font-bold mb-2">가입을 환영합니다!</h2>
          <p className="text-gray-500 text-sm mb-6">신규 가입 웰컴 쿠폰이 발급되었습니다.</p>
          <div className="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-6 mb-6">
            <p className="text-xs text-gray-400 mb-1">{welcomeCoupon.name}</p>
            <p className="text-2xl font-bold font-mono tracking-widest text-gray-900">{welcomeCoupon.code}</p>
            <p className="text-xs text-gray-400 mt-2">체크아웃 시 쿠폰 코드를 입력하세요</p>
          </div>
          <button
            onClick={() => { router.push('/'); router.refresh() }}
            className="w-full bg-gray-900 text-white py-3 rounded-lg font-medium hover:bg-gray-700 transition-colors"
          >
            쇼핑 시작하기
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-sm">
        <h1 className="text-2xl font-bold text-center mb-8">회원가입</h1>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <p className="text-sm text-red-500 bg-red-50 rounded-lg px-4 py-2">{error}</p>
          )}

          {[
            { id: 'name', label: '이름', type: 'text', required: true, autoComplete: 'name' },
            { id: 'email', label: '이메일', type: 'email', required: true, autoComplete: 'email' },
            { id: 'phone', label: '전화번호 (선택)', type: 'tel', required: false, autoComplete: 'tel' },
            { id: 'password', label: '비밀번호 (8자 이상)', type: 'password', required: true, autoComplete: 'new-password' },
            { id: 'password_confirmation', label: '비밀번호 확인', type: 'password', required: true, autoComplete: 'new-password' },
          ].map(({ id, label, type, required, autoComplete }) => (
            <div key={id}>
              <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
                {label}
              </label>
              <input
                id={id}
                name={id}
                type={type}
                required={required}
                autoComplete={autoComplete}
                value={form[id as keyof typeof form]}
                onChange={handleChange}
                className="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
              />
            </div>
          ))}

          {/* 성별/출생연도 (선택) */}
          <div className="border-t border-gray-100 pt-4">
            <p className="text-xs text-gray-400 mb-3">아래 정보는 선택 사항이며 맞춤 상품 추천에 활용됩니다.</p>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label htmlFor="gender" className="block text-sm font-medium text-gray-700 mb-1">
                  성별 (선택)
                </label>
                <select
                  id="gender"
                  name="gender"
                  value={form.gender}
                  onChange={handleChange}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white"
                >
                  <option value="">선택 안 함</option>
                  <option value="male">남성</option>
                  <option value="female">여성</option>
                  <option value="other">기타</option>
                </select>
              </div>
              <div>
                <label htmlFor="birth_year" className="block text-sm font-medium text-gray-700 mb-1">
                  출생연도 (선택)
                </label>
                <input
                  id="birth_year"
                  name="birth_year"
                  type="number"
                  min="1900"
                  max={new Date().getFullYear()}
                  placeholder="예: 1990"
                  value={form.birth_year}
                  onChange={handleChange}
                  className="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900"
                />
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-gray-900 text-white py-3 rounded-lg font-medium hover:bg-gray-700 disabled:opacity-50 transition-colors"
          >
            {loading ? '처리 중...' : '가입하기'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-gray-500">
          이미 계정이 있으신가요?{' '}
          <Link href="/login" className="text-gray-900 font-medium hover:underline">
            로그인
          </Link>
        </p>
      </div>
    </div>
  )
}
