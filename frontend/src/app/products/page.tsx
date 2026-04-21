import ProductCard from '@/components/ProductCard'
import { categoryApi, productApi, type ProductQuery } from '@/lib/api'
import Link from 'next/link'
import type { Metadata } from 'next'

export const metadata: Metadata = { title: '상품 목록' }

type SearchParams = Record<string, string | string[] | undefined>

interface Props {
  searchParams: Promise<SearchParams>
}

const SORT_OPTIONS = [
  { value: 'latest',    label: '최신순' },
  { value: 'popular',   label: '인기순' },
  { value: 'price_asc', label: '가격 낮은순' },
  { value: 'price_desc',label: '가격 높은순' },
  { value: 'rating',    label: '평점순' },
] as const

export default async function ProductsPage({ searchParams }: Props) {
  const rawParams = await searchParams
  // string[] → 첫 번째 값만 사용
  const params: Record<string, string | undefined> = Object.fromEntries(
    Object.entries(rawParams).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]),
  )

  const query: ProductQuery = {
    category:  params.category,
    search:    params.search,
    sort:      (params.sort as ProductQuery['sort']) ?? 'latest',
    page:      params.page ? Number(params.page) : 1,
    per_page:  20,
  }

  const [productsRes, categories] = await Promise.all([
    productApi.list(query),
    categoryApi.list(true),
  ])

  const currentCategory = categories.find((c) => c.slug === params.category)

  function buildHref(overrides: Partial<Record<string, string>>) {
    const next = { ...params, ...overrides }
    const qs = new URLSearchParams()
    for (const [k, v] of Object.entries(next)) {
      if (v) qs.set(k, v)
    }
    return `/products?${qs.toString()}`
  }

  return (
    <div className="mx-auto max-w-screen-xl px-4 py-8">
      {/* 헤더 */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold">
          {params.search
            ? `"${params.search}" 검색 결과`
            : currentCategory?.name ?? '전체 상품'}
        </h1>
        <p className="text-sm text-gray-400 mt-1">총 {productsRes.total.toLocaleString()}개</p>
      </div>

      <div className="flex gap-8">
        {/* 사이드바 — 카테고리 */}
        <aside className="hidden lg:block w-48 flex-shrink-0">
          <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">카테고리</p>
          <ul className="space-y-1">
            <li>
              <Link
                href={buildHref({ category: undefined, page: undefined })}
                className={`block py-1.5 px-2 text-sm rounded hover:bg-gray-50 ${!params.category ? 'font-semibold text-gray-900' : 'text-gray-600'}`}
              >
                전체
              </Link>
            </li>
            {categories
              .filter((c) => c.parent_id === null)
              .map((cat) => (
                <li key={cat.id}>
                  <Link
                    href={buildHref({ category: cat.slug, page: undefined })}
                    className={`block py-1.5 px-2 text-sm rounded hover:bg-gray-50 ${params.category === cat.slug ? 'font-semibold text-gray-900' : 'text-gray-600'}`}
                  >
                    {cat.name}
                  </Link>
                </li>
              ))}
          </ul>
        </aside>

        {/* 메인 */}
        <div className="flex-1 min-w-0">
          {/* 정렬 바 */}
          <div className="flex items-center justify-between mb-4">
            <div className="flex gap-2 overflow-x-auto scrollbar-none">
              {SORT_OPTIONS.map((opt) => (
                <Link
                  key={opt.value}
                  href={buildHref({ sort: opt.value, page: undefined })}
                  className={`whitespace-nowrap px-3 py-1.5 rounded-full text-xs border transition-colors ${
                    (query.sort ?? 'latest') === opt.value
                      ? 'bg-gray-900 text-white border-gray-900'
                      : 'text-gray-600 border-gray-200 hover:border-gray-900'
                  }`}
                >
                  {opt.label}
                </Link>
              ))}
            </div>
          </div>

          {/* 상품 그리드 */}
          {productsRes.data.length === 0 ? (
            <div className="py-24 text-center text-gray-400">
              <p className="text-lg">상품이 없습니다.</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
              {productsRes.data.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}

          {/* 페이지네이션 */}
          {productsRes.last_page > 1 && (
            <div className="mt-10 flex justify-center gap-2">
              {productsRes.current_page > 1 && (
                <Link
                  href={buildHref({ page: String(productsRes.current_page - 1) })}
                  className="px-4 py-2 border rounded text-sm hover:bg-gray-50"
                >
                  이전
                </Link>
              )}
              {Array.from({ length: Math.min(productsRes.last_page, 7) }, (_, i) => {
                const page = i + 1
                return (
                  <Link
                    key={page}
                    href={buildHref({ page: String(page) })}
                    className={`px-4 py-2 border rounded text-sm ${
                      page === productsRes.current_page
                        ? 'bg-gray-900 text-white border-gray-900'
                        : 'hover:bg-gray-50'
                    }`}
                  >
                    {page}
                  </Link>
                )
              })}
              {productsRes.current_page < productsRes.last_page && (
                <Link
                  href={buildHref({ page: String(productsRes.current_page + 1) })}
                  className="px-4 py-2 border rounded text-sm hover:bg-gray-50"
                >
                  다음
                </Link>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
