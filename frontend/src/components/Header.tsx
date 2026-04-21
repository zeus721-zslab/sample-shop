'use client'

import { useAuth } from '@/store/auth'
import { AnimatePresence, motion } from 'framer-motion'
import Link from 'next/link'
import { usePathname, useRouter } from 'next/navigation'
import { useEffect, useRef, useState } from 'react'

const NAV_ITEMS = [
  { label: '신상품', href: '/products?sort=latest' },
  { label: '패션/의류', href: '/category/fashion' },
  { label: '전자제품', href: '/category/electronics' },
  { label: '뷰티/헬스', href: '/category/beauty' },
  { label: '식품', href: '/category/food' },
  { label: '스포츠/레저', href: '/category/sports' },
  { label: '가구/인테리어', href: '/category/furniture' },
  { label: '전체보기', href: '/products' },
]

export default function Header() {
  const { user, token, clearAuth } = useAuth()
  const router = useRouter()
  const pathname = usePathname()
  const [searchOpen, setSearchOpen] = useState(false)
  const [query, setQuery] = useState('')
  const [scrolled, setScrolled] = useState(false)
  const searchRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    if (searchOpen) searchRef.current?.focus()
  }, [searchOpen])

  useEffect(() => {
    setSearchOpen(false)
  }, [pathname])

  // 스크롤 그림자
  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  function handleLogout() {
    if (token) {
      fetch(
        `${process.env.NEXT_PUBLIC_API_URL ?? 'https://zslab-shop.duckdns.org/api'}/auth/logout`,
        { method: 'POST', headers: { Authorization: `Bearer ${token}` } },
      ).catch(() => {})
    }
    clearAuth()
    router.refresh()
  }

  function handleSearch(e: React.FormEvent) {
    e.preventDefault()
    if (query.trim()) {
      router.push(`/search?q=${encodeURIComponent(query.trim())}`)
      setSearchOpen(false)
      setQuery('')
    }
  }

  return (
    <header
      className="sticky top-0 z-50 bg-white transition-shadow duration-300"
      style={{ boxShadow: scrolled ? '0 2px 16px 0 rgba(0,0,0,0.07)' : 'none' }}
    >
      {/* ── 검색 오버레이 (AnimatePresence) ─────────────────── */}
      <AnimatePresence>
        {searchOpen && (
          <motion.div
            key="search-overlay"
            initial={{ opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -4 }}
            transition={{ duration: 0.2, ease: 'easeOut' }}
            className="absolute inset-0 z-10 bg-white flex items-center px-4 h-full border-b border-gray-100"
          >
            <form onSubmit={handleSearch} className="flex-1 flex items-center gap-3 max-w-xl mx-auto">
              <svg className="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                ref={searchRef}
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="브랜드, 상품, 카테고리 검색"
                className="flex-1 text-base outline-none placeholder:text-gray-300"
              />
              <button
                type="button"
                onClick={() => { setSearchOpen(false); setQuery('') }}
                className="p-1 text-gray-400 hover:text-gray-900"
              >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M6 18 18 6M6 6l12 12" />
                </svg>
              </button>
            </form>
          </motion.div>
        )}
      </AnimatePresence>

      {/* ── 메인 헤더 ────────────────────────────────────── */}
      <div className="border-b border-gray-100">
        <div className="mx-auto max-w-screen-xl px-4 h-14 flex items-center gap-6">
          <Link
            href="/"
            className="text-[22px] font-bold tracking-[-0.04em] text-[#111] flex-shrink-0"
          >
            zslab
          </Link>

          <form
            onSubmit={handleSearch}
            className="hidden md:flex items-center gap-2 flex-1 max-w-xs lg:max-w-sm"
          >
            <div className="flex items-center w-full border border-gray-150 bg-gray-50 rounded-full px-4 py-2 gap-2 focus-within:border-gray-900 transition-colors">
              <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
              <input
                name="search"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="상품 검색"
                className="bg-transparent text-sm outline-none w-full placeholder:text-gray-400"
              />
            </div>
          </form>

          <div className="ml-auto flex items-center gap-3 text-sm">
            <button
              onClick={() => setSearchOpen(true)}
              className="md:hidden p-1 text-gray-600 hover:text-gray-900"
              aria-label="검색"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
            </button>

            {user ? (
              <>
                <Link href="/my" className="hidden sm:block text-gray-600 hover:text-gray-900 transition-colors text-xs">
                  {user.name}
                </Link>
                <button
                  onClick={handleLogout}
                  className="hidden sm:block text-gray-400 hover:text-gray-900 transition-colors text-xs"
                >
                  로그아웃
                </button>
              </>
            ) : (
              <>
                <Link href="/login" className="hidden sm:block text-gray-600 hover:text-gray-900 transition-colors text-xs">
                  로그인
                </Link>
                <Link
                  href="/register"
                  className="hidden sm:block text-xs bg-[#111] text-white px-3.5 py-1.5 rounded-full hover:bg-gray-700 transition-colors"
                >
                  회원가입
                </Link>
              </>
            )}

            <Link
              href="/cart"
              className="p-1 text-gray-600 hover:text-gray-900 transition-colors"
              aria-label="장바구니"
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
              </svg>
            </Link>
          </div>
        </div>
      </div>

      {/* ── GNB ─────────────────────────────────────────── */}
      <nav className="border-b border-gray-100">
        <div className="mx-auto max-w-screen-xl px-4 flex gap-1 overflow-x-auto scrollbar-none">
          {NAV_ITEMS.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={`py-3 px-3 text-[13px] whitespace-nowrap border-b-[1.5px] transition-colors ${
                pathname + (typeof window !== 'undefined' ? window.location.search : '') === item.href
                  ? 'border-[#111] text-[#111] font-medium'
                  : 'border-transparent text-gray-500 hover:text-[#111]'
              }`}
            >
              {item.label}
            </Link>
          ))}
        </div>
      </nav>
    </header>
  )
}
