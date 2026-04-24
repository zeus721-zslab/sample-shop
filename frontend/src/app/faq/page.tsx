'use client'

import FadeIn from '@/components/motion/FadeIn'
import { faqApi } from '@/lib/api'
import type { Faq } from '@/types'
import { AnimatePresence, motion } from 'framer-motion'
import Link from 'next/link'
import { useEffect, useState } from 'react'

const CATEGORY_ORDER = ['주문/결제', '배송', '반품/교환', '회원/계정']
const CATEGORY_ICONS: Record<string, string> = {
  '주문/결제': '💳',
  '배송': '📦',
  '반품/교환': '🔄',
  '회원/계정': '👤',
}

function AccordionItem({ faq }: { faq: Faq }) {
  const [open, setOpen] = useState(false)

  return (
    <div className="border-b border-gray-100 last:border-0">
      <button
        className="w-full text-left py-5 flex items-start justify-between gap-4 group"
        onClick={() => setOpen((v) => !v)}
        aria-expanded={open}
      >
        <div className="flex items-start gap-3">
          <span className="text-sm font-bold text-gray-400 shrink-0 mt-0.5">Q.</span>
          <span className="text-sm font-medium text-gray-900 group-hover:text-black transition-colors">
            {faq.question}
          </span>
        </div>
        <motion.span
          animate={{ rotate: open ? 45 : 0 }}
          transition={{ duration: 0.2 }}
          className="shrink-0 text-gray-400 mt-0.5 text-lg leading-none"
        >
          +
        </motion.span>
      </button>

      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className="overflow-hidden"
          >
            <div className="flex gap-3 pb-5">
              <span className="text-sm font-bold text-black shrink-0">A.</span>
              <p className="text-sm text-gray-600 leading-relaxed">{faq.answer}</p>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

export default function FaqPage() {
  const [grouped, setGrouped] = useState<Record<string, Faq[]>>({})
  const [categories, setCategories] = useState<string[]>([])
  const [activeTab, setActiveTab] = useState(CATEGORY_ORDER[0])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    faqApi
      .list()
      .then((res) => {
        setGrouped(res.data)
        const orderedCats = CATEGORY_ORDER.filter((c) => res.categories.includes(c))
        setCategories(orderedCats)
        if (orderedCats.length > 0) setActiveTab(orderedCats[0])
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [])

  const currentFaqs = grouped[activeTab] ?? []

  return (
    <div className="mx-auto max-w-screen-md px-4 py-12">
      {/* 브레드크럼 */}
      <nav className="text-xs text-gray-400 mb-8 flex items-center gap-1.5">
        <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
        <span>/</span>
        <span className="text-gray-700">자주 묻는 질문</span>
      </nav>

      <FadeIn>
        <h1 className="text-4xl font-bold tracking-tight mb-2">자주 묻는 질문</h1>
        <p className="text-gray-400 text-sm mb-10">궁금하신 내용을 빠르게 찾아보세요.</p>
      </FadeIn>

      {/* 카테고리 탭 */}
      <FadeIn delay={0.1}>
        <div className="flex gap-1 mb-8 border-b border-gray-100 overflow-x-auto scrollbar-none">
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setActiveTab(cat)}
              className={`relative shrink-0 px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap
                ${activeTab === cat ? 'text-black' : 'text-gray-400 hover:text-gray-700'}`}
            >
              <span className="mr-1.5">{CATEGORY_ICONS[cat] ?? ''}</span>
              {cat}
              {activeTab === cat && (
                <motion.span
                  layoutId="faq-tab-indicator"
                  className="absolute bottom-0 left-0 right-0 h-0.5 bg-black"
                />
              )}
            </button>
          ))}
        </div>
      </FadeIn>

      {/* FAQ 목록 */}
      <FadeIn delay={0.15}>
        {loading ? (
          <div className="py-16 text-center text-sm text-gray-400">불러오는 중...</div>
        ) : currentFaqs.length === 0 ? (
          <div className="py-16 text-center text-sm text-gray-400">등록된 FAQ가 없습니다.</div>
        ) : (
          <motion.div
            key={activeTab}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25 }}
          >
            {currentFaqs.map((faq) => (
              <AccordionItem key={faq.id} faq={faq} />
            ))}
          </motion.div>
        )}
      </FadeIn>
    </div>
  )
}
