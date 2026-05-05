import ProductCard from '@/components/ProductCard'
import StaggerList from '@/components/motion/StaggerList'
import { categoryApi, productApi, type ProductQuery } from '@/lib/api'
import Link from 'next/link'
import { notFound } from 'next/navigation'
import type { Metadata } from 'next'

type SearchParams = Record<string, string | string[] | undefined>
interface Props {
  params: Promise<{ slug: string }>
  searchParams: Promise<SearchParams>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params
  try {
    const cat = await categoryApi.get(slug)
    return { title: cat.name }
  } catch {
    return { title: '카테고리' }
  }
}

const SORT_OPTIONS = [
  { value: 'latest',     label: '최신순' },
  { value: 'popular',    label: '인기순' },
  { value: 'price_asc',  label: '가격 낮은순' },
  { value: 'price_desc', label: '가격 높은순' },
  { value: 'rating',     label: '평점순' },
] as const

export default async function CategoryPage({ params, searchParams }: Props) {
  const { slug } = await params
  const raw = await searchParams
  const str = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v) ?? ''

  const sort = (str(raw.sort) || 'latest') as ProductQuery['sort']
  const page = Math.max(1, Number(str(raw.page)) || 1)

  // 카테고리 정보 + 전체 카테고리 목록
  const [category, allCategories] = await Promise.all([
    categoryApi.get(slug).catch(() => null),
    categoryApi.list(true).catch(() => []),
  ])

  if (!category) notFound()

  // 서브카테고리 (해당 카테고리가 부모인 경우)
  const subCategories = allCategories.filter((c) => c.parent_id === category.id)

  // 부모 카테고리 (현재가 소분류인 경우)
  const parentCategory = category.parent_id
    ? allCategories.find((c) => c.id === category.parent_id) ?? null
    : null

  // 같은 부모를 가진 형제 카테고리
  const siblingCategories = parentCategory
    ? allCategories.filter((c) => c.parent_id === parentCategory.id)
    : allCategories.filter((c) => c.parent_id === null)

  // 상품 목록
  const productsRes = await productApi.list({
    category: slug,
    sort,
    page,
    per_page: 20,
  }).catch(() => ({ data: [], current_page: 1, last_page: 1, per_page: 20, total: 0 }))

  function buildHref(overrides: Record<string, string | undefined>) {
    const next = { sort: sort ?? 'latest', page: String(page), ...overrides }
    const qs = new URLSearchParams()
    for (const [k, v] of Object.entries(next)) {
      if (v) qs.set(k, v)
    }
    return `/category/${slug}?${qs.toString()}`
  }

  return (
    <div className="mx-auto max-w-screen-xl px-4 py-8">
      {/* 브레드크럼 */}
      <nav className="flex items-center gap-2 text-xs text-gray-400 mb-6">
        <Link href="/" className="hover:text-gray-900">홈</Link>
        <span>/</span>
        {parentCategory && (
          <>
            <Link href={`/category/${parentCategory.slug}`} className="hover:text-gray-900">
              {parentCategory.name}
            </Link>
            <span>/</span>
          </>
        )}
        <span className="text-gray-900 font-medium">{category.name}</span>
      </nav>

      {/* 카테고리 헤더 */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold">{category.name}</h1>
        <p className="text-sm text-gray-400 mt-1">총 {productsRes.total.toLocaleString()}개</p>
      </div>

      {/* 카테고리 탭
           - 대분류 페이지: 전체(현재) + 하위 카테고리 탭
           - 소분류 페이지: 전체(→부모) + 형제 카테고리 탭 (현재 탭 활성화)
      */}
      {(subCategories.length > 0 || parentCategory) && (
        <div className="flex gap-2 overflow-x-auto scrollbar-none mb-6 pb-2 border-b border-gray-100">
          {/* 전체 탭 */}
          <Link
            href={parentCategory ? `/category/${parentCategory.slug}` : `/category/${slug}`}
            className={`whitespace-nowrap px-4 py-2 rounded-full text-sm border transition-colors ${
              !parentCategory
                ? 'bg-gray-900 text-white border-gray-900'
                : 'text-gray-600 border-gray-200 hover:border-gray-900'
            }`}
          >
            전체
          </Link>
          {/* 하위(대분류) 또는 형제(소분류) 카테고리 탭 */}
          {(parentCategory ? siblingCategories : subCategories).map((tab) => (
            <Link
              key={tab.id}
              href={`/category/${tab.slug}`}
              className={`whitespace-nowrap px-4 py-2 rounded-full text-sm border transition-colors ${
                tab.slug === slug
                  ? 'bg-gray-900 text-white border-gray-900'
                  : 'text-gray-600 border-gray-200 hover:border-gray-900'
              }`}
            >
              {tab.name}
            </Link>
          ))}
        </div>
      )}

      {/* 형제 카테고리 사이드바 + 메인 */}
      <div className="flex gap-8">
        {/* 사이드바 */}
        <aside className="hidden lg:block w-48 flex-shrink-0">
          <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
            {parentCategory ? parentCategory.name : '카테고리'}
          </p>
          <ul className="space-y-1">
            {siblingCategories.map((cat) => (
              <li key={cat.id}>
                <Link
                  href={`/category/${cat.slug}`}
                  className={`block py-1.5 px-2 text-sm rounded hover:bg-gray-50 ${
                    cat.slug === slug ? 'font-semibold text-gray-900 bg-gray-50' : 'text-gray-600'
                  }`}
                >
                  {cat.name}
                </Link>
              </li>
            ))}
          </ul>

          {/* 서브카테고리 (모바일 숨김, 사이드에 표시) */}
          {subCategories.length > 0 && (
            <div className="mt-6">
              <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">하위 카테고리</p>
              <ul className="space-y-1">
                {subCategories.map((sub) => (
                  <li key={sub.id}>
                    <Link
                      href={`/category/${sub.slug}`}
                      className="block py-1.5 px-2 text-sm rounded text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                    >
                      {sub.name}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </aside>

        {/* 메인 */}
        <div className="flex-1 min-w-0">
          {/* 정렬 */}
          <div className="flex gap-2 overflow-x-auto scrollbar-none mb-4">
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

          {/* 상품 그리드 */}
          {productsRes.data.length === 0 ? (
            <div className="py-24 text-center">
              <p className="text-lg text-gray-400 mb-2">상품이 없습니다.</p>
              <p className="text-sm text-gray-300 mb-6">다른 카테고리를 둘러보세요.</p>
              <Link href="/products" className="inline-block bg-gray-900 text-white px-8 py-3 rounded-full text-sm hover:bg-gray-700">
                전체 상품 보기
              </Link>
            </div>
          ) : (
            <StaggerList
              className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6"
              stagger={0.05}
              y={18}
              duration={0.38}
            >
              {productsRes.data.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </StaggerList>
          )}

          {/* 페이지네이션 */}
          {productsRes.last_page > 1 && (
            <div className="mt-10 flex justify-center gap-2 flex-wrap">
              {page > 1 && (
                <Link href={buildHref({ page: String(page - 1) })} className="px-4 py-2 border rounded text-sm hover:bg-gray-50">이전</Link>
              )}
              {Array.from({ length: Math.min(productsRes.last_page, 7) }, (_, i) => {
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
              {page < productsRes.last_page && (
                <Link href={buildHref({ page: String(page + 1) })} className="px-4 py-2 border rounded text-sm hover:bg-gray-50">다음</Link>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
