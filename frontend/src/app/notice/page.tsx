import FadeIn from '@/components/motion/FadeIn'
import { noticeApi } from '@/lib/api'
import type { Metadata } from 'next'
import Link from 'next/link'

export const metadata: Metadata = { title: '공지사항' }

const CATEGORY_LABELS: Record<string, string> = {
  general: '일반',
  event: '이벤트',
  policy: '정책',
  delivery: '배송',
  system: '시스템',
}
const CATEGORY_COLORS: Record<string, string> = {
  general: 'bg-gray-100 text-gray-600',
  event: 'bg-blue-50 text-blue-600',
  policy: 'bg-purple-50 text-purple-600',
  delivery: 'bg-yellow-50 text-yellow-700',
  system: 'bg-red-50 text-red-600',
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('ko-KR', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

export default async function NoticePage() {
  const res = await noticeApi.list().catch(() => null)
  const notices = res?.data ?? []

  return (
    <div className="mx-auto max-w-screen-md px-4 py-12">
      {/* 브레드크럼 */}
      <nav className="text-xs text-gray-400 mb-8 flex items-center gap-1.5">
        <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
        <span>/</span>
        <span className="text-gray-700">공지사항</span>
      </nav>

      <FadeIn>
        <h1 className="text-4xl font-bold tracking-tight mb-2">공지사항</h1>
        <p className="text-gray-400 text-sm mb-12">zslab shop의 새로운 소식을 전해드립니다.</p>
      </FadeIn>

      <FadeIn delay={0.1}>
        <div className="divide-y divide-gray-100">
          {notices.length === 0 && (
            <p className="py-16 text-center text-gray-400 text-sm">등록된 공지사항이 없습니다.</p>
          )}
          {notices.map((notice) => (
            <Link
              key={notice.id}
              href={`/notice/${notice.id}`}
              className="flex items-start gap-4 py-5 group hover:bg-gray-50 -mx-4 px-4 transition-colors rounded-lg"
            >
              {notice.is_pinned && (
                <span className="mt-0.5 shrink-0 text-red-500 text-xs font-bold">PIN</span>
              )}
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1.5">
                  <span className={`text-[11px] font-medium px-2 py-0.5 rounded-full ${CATEGORY_COLORS[notice.category] ?? 'bg-gray-100 text-gray-600'}`}>
                    {CATEGORY_LABELS[notice.category] ?? notice.category}
                  </span>
                </div>
                <p className={`text-sm font-medium group-hover:underline truncate ${notice.is_pinned ? 'font-bold' : ''}`}>
                  {notice.title}
                </p>
              </div>
              <time className="shrink-0 text-xs text-gray-400 mt-0.5">{formatDate(notice.created_at)}</time>
            </Link>
          ))}
        </div>
      </FadeIn>
    </div>
  )
}
