import FadeIn from '@/components/motion/FadeIn'
import ScrollReveal from '@/components/motion/ScrollReveal'
import type { Metadata } from 'next'
import Link from 'next/link'

export const metadata: Metadata = { title: '배송 안내' }

const SHIPPING_CARDS = [
  {
    icon: '🚚',
    title: '기본 배송비',
    desc: '3,000원',
    sub: '50,000원 이상 구매 시 무료',
  },
  {
    icon: '📅',
    title: '배송 기간',
    desc: '2~3 영업일',
    sub: '주문 접수 후 평균 기준',
  },
  {
    icon: '📍',
    title: '배송 지역',
    desc: '전국 배송',
    sub: '도서 산간 지역 추가 배송비 발생',
  },
  {
    icon: '🕐',
    title: '출고 마감',
    desc: '오후 2시',
    sub: '마감 이후 주문은 익일 출고',
  },
]

const STEPS = [
  { label: '주문 완료', desc: '결제 확인 즉시' },
  { label: '상품 준비', desc: '1~2 영업일' },
  { label: '배송 출발', desc: '택배사 인계' },
  { label: '배송 완료', desc: '2~3 영업일' },
]

const NOTICES = [
  '공휴일, 연휴 기간에는 배송이 지연될 수 있습니다.',
  '제주 및 도서 산간 지역은 2~5 영업일이 추가 소요됩니다.',
  '일부 대형 상품 또는 특수 상품은 별도 배송비가 적용될 수 있습니다.',
  '받으시는 분의 부재 시 경비실 보관 또는 문 앞 배송이 될 수 있습니다.',
  '배송 중 파손/분실 사고 발생 시 고객센터로 즉시 연락해 주세요.',
]

export default function ShippingPage() {
  return (
    <div className="mx-auto max-w-screen-md px-4 py-12">
      {/* 브레드크럼 */}
      <nav className="text-xs text-gray-400 mb-8 flex items-center gap-1.5">
        <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
        <span>/</span>
        <span className="text-gray-700">배송 안내</span>
      </nav>

      <FadeIn>
        <h1 className="text-4xl font-bold tracking-tight mb-2">배송 안내</h1>
        <p className="text-gray-400 text-sm mb-12">빠르고 안전하게 배송해 드립니다.</p>
      </FadeIn>

      {/* 배송 정책 카드 */}
      <ScrollReveal>
        <div className="grid grid-cols-2 gap-4 mb-14">
          {SHIPPING_CARDS.map((card) => (
            <div key={card.title} className="border border-gray-100 rounded-2xl p-6 bg-white hover:shadow-sm transition-shadow">
              <div className="text-3xl mb-3">{card.icon}</div>
              <p className="text-xs text-gray-400 mb-1">{card.title}</p>
              <p className="text-xl font-bold text-gray-900 mb-1">{card.desc}</p>
              <p className="text-xs text-gray-400">{card.sub}</p>
            </div>
          ))}
        </div>
      </ScrollReveal>

      {/* 배송 흐름 다이어그램 */}
      <ScrollReveal delay={0.1}>
        <h2 className="text-xl font-bold mb-6">배송 프로세스</h2>
        <div className="flex items-center justify-between mb-14 overflow-x-auto gap-2">
          {STEPS.map((step, i) => (
            <div key={step.label} className="flex items-center shrink-0">
              <div className="text-center">
                <div className="w-12 h-12 rounded-full bg-black text-white flex items-center justify-center text-sm font-bold mx-auto mb-2">
                  {i + 1}
                </div>
                <p className="text-xs font-semibold text-gray-900">{step.label}</p>
                <p className="text-[11px] text-gray-400 mt-0.5">{step.desc}</p>
              </div>
              {i < STEPS.length - 1 && (
                <div className="w-8 h-px bg-gray-200 mx-3 shrink-0" />
              )}
            </div>
          ))}
        </div>
      </ScrollReveal>

      {/* 유의사항 */}
      <ScrollReveal delay={0.15}>
        <h2 className="text-xl font-bold mb-4">배송 유의사항</h2>
        <ul className="space-y-3">
          {NOTICES.map((text) => (
            <li key={text} className="flex items-start gap-3 text-sm text-gray-600">
              <span className="mt-1 shrink-0 w-1.5 h-1.5 rounded-full bg-gray-300" />
              {text}
            </li>
          ))}
        </ul>
      </ScrollReveal>

      {/* 문의 */}
      <ScrollReveal delay={0.2}>
        <div className="mt-14 bg-gray-50 rounded-2xl p-6">
          <p className="text-sm font-semibold text-gray-900 mb-1">배송 관련 문의</p>
          <p className="text-sm text-gray-500">
            평일 09:00 ~ 18:00 (점심 12:00 ~ 13:00 제외)
            <br />
            이메일: <a href="mailto:help@zslab.com" className="text-gray-900 font-medium hover:underline">help@zslab.com</a>
          </p>
        </div>
      </ScrollReveal>
    </div>
  )
}
