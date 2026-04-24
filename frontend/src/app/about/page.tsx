'use client'

import FadeIn from '@/components/motion/FadeIn'
import ScrollReveal from '@/components/motion/ScrollReveal'
import { motion, useInView } from 'framer-motion'
import Link from 'next/link'
import { useEffect, useRef, useState } from 'react'

const VALUES = [
  {
    icon: '◎',
    title: 'Curated',
    desc: '단순히 많은 상품이 아닌, 엄선된 취향의 상품만을 소개합니다. 우리가 직접 써보고 선택한 것들만이 이곳에 존재합니다.',
  },
  {
    icon: '◈',
    title: 'Editorial',
    desc: '상품이 아닌 라이프스타일을 제안합니다. 각 상품은 하나의 이야기를 담고 있으며, 당신의 일상을 더 풍요롭게 만들기 위해 기획되었습니다.',
  },
  {
    icon: '◉',
    title: 'Honest',
    desc: '과장 없이 솔직하게. 상품의 좋은 점과 아쉬운 점을 모두 이야기합니다. 고객이 현명한 선택을 할 수 있도록 돕는 것이 우리의 역할입니다.',
  },
]

const STATS = [
  { label: '엄선된 상품', value: 50, unit: '개+' },
  { label: '누적 주문', value: 120, unit: '건+' },
  { label: '회원 수', value: 200, unit: '명+' },
]

function CountUp({ target, unit }: { target: number; unit: string }) {
  const [count, setCount] = useState(0)
  const ref = useRef<HTMLSpanElement>(null)
  const inView = useInView(ref, { once: true })

  useEffect(() => {
    if (!inView) return
    let start = 0
    const duration = 1200
    const step = target / (duration / 16)
    const timer = setInterval(() => {
      start += step
      if (start >= target) {
        setCount(target)
        clearInterval(timer)
      } else {
        setCount(Math.floor(start))
      }
    }, 16)
    return () => clearInterval(timer)
  }, [inView, target])

  return (
    <span ref={ref} className="text-5xl font-bold tracking-tight">
      {count}
      <span className="text-2xl text-gray-400 ml-1">{unit}</span>
    </span>
  )
}

export default function AboutPage() {
  return (
    <div className="overflow-hidden">
      {/* 브레드크럼 */}
      <div className="mx-auto max-w-screen-xl px-4 pt-8 pb-0">
        <nav className="text-xs text-gray-400 flex items-center gap-1.5">
          <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
          <span>/</span>
          <span className="text-gray-700">브랜드 소개</span>
        </nav>
      </div>

      {/* 히어로 */}
      <section className="relative bg-black text-white mt-8 py-28 px-4">
        <div className="absolute inset-0 overflow-hidden pointer-events-none">
          <p className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[120px] sm:text-[180px] font-black opacity-[0.04] tracking-tighter whitespace-nowrap select-none">
            ZSLAB
          </p>
        </div>
        <div className="relative mx-auto max-w-screen-md text-center">
          <FadeIn>
            <p className="text-xs tracking-[0.3em] text-gray-400 mb-6 uppercase">Since 2026</p>
            <h1 className="text-5xl sm:text-6xl font-black tracking-tight mb-6 leading-none">
              zslab shop
            </h1>
            <p className="text-lg text-gray-300 leading-relaxed font-light">
              패션부터 라이프스타일까지,
              <br className="hidden sm:block" />
              취향을 발견하는 공간
            </p>
          </FadeIn>
        </div>
      </section>

      {/* 브랜드 스토리 */}
      <section className="mx-auto max-w-screen-md px-4 py-20">
        <ScrollReveal>
          <h2 className="text-3xl font-bold mb-8">우리의 이야기</h2>
          <div className="space-y-5 text-gray-600 text-base leading-[1.9]">
            <p>
              zslab은 "취향"이라는 단어에서 시작했습니다. 수많은 상품들 속에서 정말로 나에게 맞는, 나의 라이프스타일을 반영하는 것을 찾기란 쉽지 않습니다.
            </p>
            <p>
              우리는 그 번거로움을 덜어드리고 싶었습니다. 직접 사용해보고, 직접 감동받은 것들만을 엄선하여 소개하는 공간. 그것이 zslab shop의 시작이었습니다.
            </p>
            <p>
              트렌드를 쫓기보다는 본질에 집중합니다. 오래 써도 질리지 않는 것, 매일 사용하고 싶어지는 것, 선물하고 싶어지는 것. 그런 상품들로만 채워진 공간을 만들고 있습니다.
            </p>
          </div>
        </ScrollReveal>
      </section>

      {/* 핵심 가치 */}
      <section className="bg-[#f7f7f5] py-20 px-4">
        <div className="mx-auto max-w-screen-md">
          <ScrollReveal>
            <h2 className="text-3xl font-bold mb-12">핵심 가치</h2>
          </ScrollReveal>
          <div className="grid gap-6">
            {VALUES.map((val, i) => (
              <ScrollReveal key={val.title} delay={i * 0.1}>
                <motion.div
                  whileHover={{ x: 4 }}
                  transition={{ duration: 0.2 }}
                  className="bg-white rounded-2xl p-8 flex gap-6 items-start"
                >
                  <span className="text-2xl shrink-0 mt-1">{val.icon}</span>
                  <div>
                    <p className="text-xs tracking-[0.2em] text-gray-400 uppercase mb-2">{val.title}</p>
                    <p className="text-gray-700 leading-relaxed text-sm">{val.desc}</p>
                  </div>
                </motion.div>
              </ScrollReveal>
            ))}
          </div>
        </div>
      </section>

      {/* 숫자로 보는 zslab */}
      <section className="mx-auto max-w-screen-md px-4 py-20">
        <ScrollReveal>
          <h2 className="text-3xl font-bold mb-12">숫자로 보는 zslab</h2>
        </ScrollReveal>
        <div className="grid grid-cols-3 gap-6 text-center">
          {STATS.map((stat, i) => (
            <ScrollReveal key={stat.label} delay={i * 0.1}>
              <div>
                <CountUp target={stat.value} unit={stat.unit} />
                <p className="text-xs text-gray-400 mt-2">{stat.label}</p>
              </div>
            </ScrollReveal>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="border-t border-gray-100 py-16 text-center px-4">
        <FadeIn>
          <p className="text-sm text-gray-400 mb-6">지금 바로 취향의 공간을 경험해 보세요</p>
          <Link
            href="/products"
            className="inline-block bg-black text-white text-sm font-medium px-8 py-3.5 rounded-full hover:bg-gray-800 transition-colors"
          >
            쇼핑 시작하기
          </Link>
        </FadeIn>
      </section>
    </div>
  )
}
