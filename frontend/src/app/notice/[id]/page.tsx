import FadeIn from '@/components/motion/FadeIn'
import { noticeApi } from '@/lib/api'
import type { Metadata } from 'next'
import Link from 'next/link'
import { notFound } from 'next/navigation'

export const dynamic = 'force-dynamic'

interface Props {
  params: Promise<{ id: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { id } = await params
  const notice = await noticeApi.get(Number(id)).catch(() => null)
  return { title: notice?.title ?? '공지사항' }
}

const CATEGORY_LABELS: Record<string, string> = {
  general: '일반',
  event: '이벤트',
  policy: '정책',
  delivery: '배송',
  system: '시스템',
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('ko-KR', { year: 'numeric', month: 'long', day: 'numeric' })
}

export default async function NoticeDetailPage({ params }: Props) {
  const { id } = await params
  const notice = await noticeApi.get(Number(id)).catch(() => null)

  if (!notice) notFound()

  return (
    <div className="mx-auto max-w-screen-md px-4 py-12">
      {/* 브레드크럼 */}
      <nav className="text-xs text-gray-400 mb-8 flex items-center gap-1.5">
        <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
        <span>/</span>
        <Link href="/notice" className="hover:text-gray-700 transition-colors">공지사항</Link>
        <span>/</span>
        <span className="text-gray-700 truncate max-w-[200px]">{notice.title}</span>
      </nav>

      <FadeIn>
        {/* 카테고리 + 핀 */}
        <div className="flex items-center gap-2 mb-4">
          <span className="text-xs font-medium text-gray-500 border border-gray-200 px-2.5 py-1 rounded-full">
            {CATEGORY_LABELS[notice.category] ?? notice.category}
          </span>
          {notice.is_pinned && (
            <span className="text-xs font-bold text-red-500 border border-red-200 px-2.5 py-1 rounded-full">
              중요
            </span>
          )}
        </div>

        <h1 className="text-3xl font-bold tracking-tight leading-tight mb-4">{notice.title}</h1>

        <time className="text-sm text-gray-400 block mb-12">{formatDate(notice.created_at)}</time>

        {/* 본문 */}
        <div
          className="prose prose-sm max-w-none text-gray-700 leading-relaxed
            [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3
            [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:mt-6 [&_h3]:mb-2
            [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-4
            [&_li]:mb-1 [&_strong]:font-bold"
          dangerouslySetInnerHTML={{ __html: notice.content }}
        />

        <div className="mt-16 pt-8 border-t border-gray-100">
          <Link
            href="/notice"
            className="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition-colors"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            목록으로
          </Link>
        </div>
      </FadeIn>
    </div>
  )
}
