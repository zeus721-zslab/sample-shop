'use client'

import { AnimatePresence, motion } from 'framer-motion'
import Image from 'next/image'
import { useRouter } from 'next/navigation'
import { useCallback, useEffect, useRef, useState } from 'react'

interface Suggestion {
  id: number
  name: string
  slug: string
  image: string | null
}

interface Props {
  value: string
  onChange: (v: string) => void
  onSearch: (q: string) => void
  placeholder?: string
  inputClassName?: string
}

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'https://zslab-shop.duckdns.org/api'

export default function SearchAutocomplete({
  value,
  onChange,
  onSearch,
  placeholder = '상품 검색',
  inputClassName = '',
}: Props) {
  const router = useRouter()
  const [suggestions, setSuggestions] = useState<Suggestion[]>([])
  const [open, setOpen] = useState(false)
  const [activeIdx, setActiveIdx] = useState(-1)
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const containerRef = useRef<HTMLDivElement>(null)

  /* ── debounce 자동완성 fetch ─────────────────────────── */
  useEffect(() => {
    if (timerRef.current) clearTimeout(timerRef.current)

    const q = value.trim()
    if (q.length === 0) {
      setSuggestions([])
      setOpen(false)
      setActiveIdx(-1)
      return
    }

    timerRef.current = setTimeout(async () => {
      try {
        const res = await fetch(
          `${API_BASE}/search/suggest?q=${encodeURIComponent(q)}`,
          { cache: 'no-store' },
        )
        if (!res.ok) return
        const data = await res.json()
        const items: Suggestion[] = data.suggestions ?? []
        setSuggestions(items)
        setOpen(items.length > 0)
        setActiveIdx(-1)
      } catch {
        // 네트워크 오류 무시
      }
    }, 300)

    return () => {
      if (timerRef.current) clearTimeout(timerRef.current)
    }
  }, [value])

  /* ── 바깥 클릭 시 드롭다운 닫기 ──────────────────────── */
  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false)
        setActiveIdx(-1)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  /* ── 항목 선택 ───────────────────────────────────────── */
  const selectSuggestion = useCallback(
    (name: string) => {
      onChange(name)
      setOpen(false)
      setActiveIdx(-1)
      onSearch(name)
    },
    [onChange, onSearch],
  )

  /* ── 키보드 내비게이션 ────────────────────────────────── */
  function handleKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
    if (!open) return

    if (e.key === 'ArrowDown') {
      e.preventDefault()
      setActiveIdx((prev) => (prev + 1) % suggestions.length)
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setActiveIdx((prev) => (prev - 1 + suggestions.length) % suggestions.length)
    } else if (e.key === 'Enter' && activeIdx >= 0) {
      e.preventDefault()
      selectSuggestion(suggestions[activeIdx].name)
    } else if (e.key === 'Escape') {
      setOpen(false)
      setActiveIdx(-1)
    }
  }

  return (
    <div ref={containerRef} className="relative w-full">
      <input
        value={value}
        onChange={(e) => onChange(e.target.value)}
        onKeyDown={handleKeyDown}
        onFocus={() => suggestions.length > 0 && setOpen(true)}
        placeholder={placeholder}
        className={inputClassName}
        autoComplete="off"
      />

      <AnimatePresence>
        {open && (
          <motion.ul
            key="suggest-dropdown"
            initial={{ opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -4 }}
            transition={{ duration: 0.15, ease: 'easeOut' }}
            className="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
            role="listbox"
          >
            {suggestions.map((s, idx) => (
              <li
                key={s.id}
                role="option"
                aria-selected={idx === activeIdx}
                onMouseDown={(e) => {
                  e.preventDefault() // blur 방지
                  selectSuggestion(s.name)
                }}
                onMouseEnter={() => setActiveIdx(idx)}
                className={`flex items-center gap-3 px-4 py-2.5 cursor-pointer text-sm transition-colors ${
                  idx === activeIdx ? 'bg-gray-50' : 'hover:bg-gray-50'
                }`}
              >
                {/* 썸네일 */}
                {s.image ? (
                  <Image
                    src={s.image}
                    alt={s.name}
                    width={32}
                    height={32}
                    className="w-8 h-8 rounded object-cover flex-shrink-0"
                  />
                ) : (
                  <div className="w-8 h-8 rounded bg-gray-100 flex-shrink-0" />
                )}

                {/* 이름 — 검색어 강조 */}
                <span
                  className="flex-1 truncate"
                  dangerouslySetInnerHTML={{
                    __html: highlightMatch(s.name, value.trim()),
                  }}
                />

                {/* 검색 아이콘 힌트 */}
                <svg
                  className="w-3.5 h-3.5 text-gray-300 flex-shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={1.5}
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                  />
                </svg>
              </li>
            ))}
          </motion.ul>
        )}
      </AnimatePresence>
    </div>
  )
}

/* ── 검색어 강조 헬퍼 ──────────────────────────────────── */
function highlightMatch(text: string, query: string): string {
  if (!query) return text
  const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  return text.replace(
    new RegExp(`(${escaped})`, 'gi'),
    '<mark class="bg-transparent font-semibold text-[#111]">$1</mark>',
  )
}
