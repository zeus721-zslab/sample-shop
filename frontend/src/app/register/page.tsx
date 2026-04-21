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
  })
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
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
      const data = await authApi.register(form)
      setAuth(data.user, data.token)
      router.push('/')
      router.refresh()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : '회원가입 중 오류가 발생했습니다.')
    } finally {
      setLoading(false)
    }
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
