import ProductCard from '@/components/ProductCard'
import { searchApi, productApi } from '@/lib/api'
import Link from 'next/link'
import type { Metadata } from 'next'

type SearchParams = Record<string, string | string[] | undefined>
interface Props { searchParams: Promise<SearchParams> }

export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const raw = await searchParams
  const q = Array.isArray(raw.q) ? raw.q[0] : raw.q
  return { title: q ? `"${q}" 검색 결과` : '검색' }
}

const SORT_OPTIONS = [
  { value: 'latest',     label: '최신순' },
  { value: 'popular',    label: '인기순' },
  { value: 'price_asc',  label: '가격 낮은순' },
  { value: 'price_desc', label: '가격 높은순' },
  { value: 'rating',     label: '평점순' },
] as const

// 검색어에 하이라이트 span 삽입 (서버측 텍스트 처리)
function highlight(text: string, query: string): string {
  if (!query.trim()) return text
  const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark class="bg-yellow-100 text-inherit not-italic">$1</mark>')
}

export default async function SearchPage({ searchParams }: Props) {
  const raw = await searchParams
  const str = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v) ?? ''

  const q    = str(raw.q)
  const sort = (str(raw.sort) || 'latest') as 'latest' | 'popular' | 'price_asc' | 'price_desc' | 'rating'
  const page = Math.max(1, Number(str(raw.page)) || 1)

  function buildHref(overrides: Record<string, string | undefined>) {
    const next = { q, sort, page: String(page), ...overrides }
    const qs = new URLSearchParams()
    for (const [k, v] of Object.entries(next)) {
      if (v) qs.set(k, v)
    }
    return `/search?${qs.toString()}`
  }

  // 검색어 없으면 인기상품 fallback
  let result = { data: [] as Awaited<ReturnType<typeof searchApi.search>>['data'], current_page: 1, last_page: 1, per_page: 20, total: 0 }
  let usedFallback = false

  if (q.trim()) {
    try {
      result = await searchApi.search(q, page, 20)
    } catch {
      // ES 에러 시 productApi fallback
      try {
        result = await productApi.list({ search: q, sort, page, per_page: 20 })
        usedFallback = true
      } catch { /* silent */ }
    }
  } else {
    try {
      result = await productApi.list({ sort: 'popular', page, per_page: 20 })
    } catch { /* silent */ }
  }

  const hasQuery = q.trim().length > 0

  return (
    <div className="mx-auto max-w-screen-xl px-4 py-8">
      {/* 검색 헤더 */}
      <div className="mb-8">
        {hasQuery ? (
          <>
            <p className="text-xs text-gray-400 mb-1 tracking-wider uppercase">Search</p>
            <h1 className="text-2xl font-bold">
              <span
                dangerouslySetInnerHTML={{ __html: `"${highlight(q, q)}"` }}
              />
            </h1>
            <p className="text-sm text-gray-400 mt-2">
              총 <span className="text-gray-900 font-medium">{result.total.toLocaleString()}</span>개의 결과
              {usedFallback && <span className="ml-2 text-xs text-amber-500">(기본 검색)</span>}
            </p>
          </>
        ) : (
          <>
            <h1 className="text-2xl font-bold">인기 상품</h1>
            <p className="text-sm text-gray-400 mt-1">지금 가장 많이 팔리는 상품들</p>
          </>
        )}
      </div>

      {/* 검색창 (재검색) */}
      <form action="/search" method="get" className="mb-8">
        <div className="flex gap-2 max-w-xl">
          <div className="flex-1 flex items-center border border-gray-200 rounded-full px-5 py-3 gap-3 focus-within:border-gray-900 transition-colors bg-white">
            <svg className="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
              name="q"
              defaultValue={q}
              placeholder="브랜드, 상품, 카테고리 검색"
              className="flex-1 text-sm outline-none placeholder:text-gray-400 bg-transparent"
            />
          </div>
          <button
            type="submit"
            className="px-6 py-3 bg-gray-900 text-white text-sm rounded-full hover:bg-gray-700 transition-colors"
          >
            검색
          </button>
        </div>
      </form>

      {/* 정렬 */}
      {result.total > 0 && (
        <div className="flex gap-2 overflow-x-auto scrollbar-none mb-6">
          {SORT_OPTIONS.map((opt) => (
            <Link
              key={opt.value}
              href={buildHref({ sort: opt.value, page: '1' })}
              className={`whitespace-nowrap px-3 py-1.5 rounded-full text-xs border transition-colors ${
                sort === opt.value
                  ? 'bg-gray-900 text-white border-gray-900'
                  : 'text-gray-600 border-gray-200 hover:border-gray-900'
              }`}
            >
              {opt.label}
            </Link>
          ))}
        </div>
      )}

      {/* 결과 없음 */}
      {hasQuery && result.total === 0 ? (
        <div className="py-24 text-center">
          <svg className="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
          </svg>
          <p className="text-gray-400 text-lg mb-2">&quot;{q}&quot;에 대한 검색 결과가 없습니다.</p>
          <p className="text-gray-300 text-sm mb-6">다른 검색어를 입력하거나 카테고리를 둘러보세요.</p>
          <Link
            href="/products"
            className="inline-block bg-gray-900 text-white px-8 py-3 rounded-full text-sm hover:bg-gray-700 transition-colors"
          >
            전체 상품 보기
          </Link>
        </div>
      ) : (
        <>
          {/* 상품 그리드 */}
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            {result.data.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>

          {/* 페이지네이션 */}
          {result.last_page > 1 && (
            <div className="mt-10 flex justify-center gap-2 flex-wrap">
              {page > 1 && (
                <Link href={buildHref({ page: String(page - 1) })} className="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                  이전
                </Link>
              )}
              {Array.from({ length: Math.min(result.last_page, 7) }, (_, i) => {
                const p = i + 1
                return (
                  <Link
                    key={p}
                    href={buildHref({ page: String(p) })}
                    className={`px-4 py-2 border rounded text-sm ${p === page ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50'}`}
                  >
                    {p}
                  </Link>
                )
              })}
              {page < result.last_page && (
                <Link href={buildHref({ page: String(page + 1) })} className="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                  다음
                </Link>
              )}
            </div>
          )}
        </>
      )}
    </div>
  )
}
