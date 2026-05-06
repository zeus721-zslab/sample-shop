import ProductCard from '@/components/ProductCard'
import RecommendationSection from '@/components/RecommendationSection'
import FadeIn from '@/components/motion/FadeIn'
import ScrollReveal from '@/components/motion/ScrollReveal'
import StaggerList from '@/components/motion/StaggerList'
import { productApi } from '@/lib/api'
import { formatPrice } from '@/lib/format'
import type { Product } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import { Suspense } from 'react'

/* ── 데이터 패칭 헬퍼 ──────────────────────────────────────────────────────── */
async function fetchProducts(params: Parameters<typeof productApi.list>[0]): Promise<Product[]> {
  try {
    const res = await productApi.list(params)
    return res.data
  } catch {
    return []
  }
}

/* ── 히어로 ────────────────────────────────────────────────────────────────── */
function HeroBanner() {
  return (
    <section className="relative bg-[#0f0f0f] text-white overflow-hidden">
      <div className="mx-auto max-w-screen-xl px-4 py-28 md:py-40 lg:py-52">
        <div className="max-w-3xl">
          <FadeIn delay={0.1} y={10}>
            <p className="text-[11px] tracking-[0.35em] text-gray-500 uppercase mb-6">
              2026 Spring / Summer
            </p>
          </FadeIn>
          <FadeIn delay={0.22} y={20}>
            <h1 className="text-[clamp(2.8rem,8vw,6rem)] font-bold leading-[1.05] tracking-[-0.03em] editorial-heading">
              좋아하는 것들로
              <br />
              <span className="text-gray-500">가득한 하루.</span>
            </h1>
          </FadeIn>
          <FadeIn delay={0.38} y={16}>
            <p className="mt-8 text-[15px] text-gray-400 max-w-sm leading-relaxed">
              취향을 존중하는 큐레이션. 패션부터 라이프스타일까지,
              <br />
              zslab이 엄선한 것들을 만나보세요.
            </p>
          </FadeIn>
          <FadeIn delay={0.5} y={12}>
            <div className="mt-10 flex items-center gap-8">
              <Link
                href="/products?sort=latest"
                className="inline-flex items-center gap-2 text-white text-sm font-medium border-b border-white pb-0.5 hover:text-gray-300 hover:border-gray-300 transition-colors"
              >
                신상품 보기
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
              </Link>
              <Link href="/products" className="text-sm text-gray-500 hover:text-white transition-colors">
                전체 상품
              </Link>
            </div>
          </FadeIn>
        </div>
      </div>

      {/* 우측 장식 — 대형 텍스트 */}
      <div
        className="absolute right-0 top-1/2 -translate-y-1/2 text-[clamp(6rem,18vw,16rem)] font-black text-white/[0.03] leading-none select-none pointer-events-none hidden lg:block tracking-[-0.06em]"
        aria-hidden
      >
        zslab
      </div>
    </section>
  )
}

/* ── 카테고리 퀵메뉴 ───────────────────────────────────────────────────────── */
const CATEGORIES = [
  { label: '패션', sub: 'Fashion', slug: 'fashion' },
  { label: '전자제품', sub: 'Electronics', slug: 'electronics' },
  { label: '뷰티', sub: 'Beauty', slug: 'beauty' },
  { label: '식품', sub: 'Food', slug: 'food' },
  { label: '스포츠', sub: 'Sports', slug: 'sports' },
  { label: '인테리어', sub: 'Interior', slug: 'furniture' },
]

function CategoryQuickMenu() {
  return (
    <section className="border-b border-gray-100">
      <div className="mx-auto max-w-screen-xl px-4">
        <StaggerList
          className="flex overflow-x-auto scrollbar-none"
          stagger={0.06}
          y={12}
          duration={0.35}
        >
          {CATEGORIES.map((cat) => (
            <Link
              key={cat.slug}
              href={`/category/${cat.slug}`}
              className="flex-shrink-0 flex flex-col items-center gap-1 px-6 py-5 hover:bg-gray-50 transition-colors group border-b-2 border-transparent hover:border-[#111]"
            >
              <span className="text-[13px] font-medium text-[#111] group-hover:text-[#111]">
                {cat.label}
              </span>
              <span className="text-[10px] text-gray-400 tracking-wider">
                {cat.sub}
              </span>
            </Link>
          ))}
        </StaggerList>
      </div>
    </section>
  )
}

/* ── 에디토리얼 피드 (잡지풍 그리드) ──────────────────────────────────────── */
async function EditorialFeed() {
  const products = await fetchProducts({ sort: 'popular', per_page: 6 })

  if (products.length < 2) return null

  const [p1, p2, p3, p4, p5, p6] = products

  return (
    <section className="mx-auto max-w-screen-xl px-4 py-16">
      {/* 섹션 헤더 */}
      <ScrollReveal y={16} duration={0.4}>
        <div className="flex items-end justify-between mb-8">
          <div>
            <p className="text-[11px] text-gray-400 tracking-[0.2em] uppercase mb-1">Editor&apos;s Pick</p>
            <h2 className="text-2xl font-bold tracking-tight">지금 가장 주목받는</h2>
          </div>
          <Link
            href="/products?sort=popular"
            className="text-[12px] text-gray-400 hover:text-[#111] transition-colors link-underline"
          >
            전체보기
          </Link>
        </div>
      </ScrollReveal>

      {/* 메인 그리드 — 잡지풍 비대칭 */}
      <div className="grid grid-cols-12 grid-rows-[auto_auto] gap-3 md:gap-4">
        {p1 && (
          <ScrollReveal className="col-span-12 md:col-span-7 row-span-2 h-full" delay={0.05}>
            <EditorialCard product={p1} large />
          </ScrollReveal>
        )}
        {p2 && (
          <ScrollReveal className="col-span-6 md:col-span-5" delay={0.15}>
            <EditorialCard product={p2} />
          </ScrollReveal>
        )}
        {p3 && (
          <ScrollReveal className="col-span-6 md:col-span-5" delay={0.25}>
            <EditorialCard product={p3} />
          </ScrollReveal>
        )}
      </div>

      {/* 보조 그리드 — 3열 */}
      {products.length >= 5 && (
        <StaggerList
          className="grid grid-cols-3 gap-3 md:gap-4 mt-3 md:mt-4"
          stagger={0.1}
          y={20}
        >
          {[p4, p5, p6].filter(Boolean).map((p) => (
            <EditorialCard key={p!.id} product={p!} />
          ))}
        </StaggerList>
      )}
    </section>
  )
}

function EditorialCard({ product, large = false }: { product: Product; large?: boolean }) {
  const hasSale = product.sale_price !== null && product.sale_price < product.price

  return (
    <Link href={`/products/${product.slug}`} className={`group block relative overflow-hidden${large ? ' h-full' : ''}`}>
      <div className={`relative w-full bg-gray-100${large ? ' h-full min-h-[300px]' : ' aspect-[3/4]'}`}>
        <Image
          src={product.images[0] ?? `https://picsum.photos/seed/${product.slug}/600/800`}
          alt={product.name}
          fill
          className="object-cover transition-transform duration-700 group-hover:scale-[1.04]"
          sizes={large ? '(max-width: 768px) 100vw, 58vw' : '(max-width: 640px) 50vw, 25vw'}
        />

        {/* 그라데이션 오버레이 */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-80" />

        {/* 텍스트 오버레이 */}
        <div className="absolute bottom-0 left-0 right-0 p-4 md:p-5 translate-y-1 group-hover:translate-y-0 transition-transform duration-300">
          {product.category && (
            <p className="text-[10px] text-white/60 tracking-[0.2em] uppercase mb-1">
              {product.category.name}
            </p>
          )}
          <p className={`text-white font-semibold leading-snug line-clamp-2 ${large ? 'text-base md:text-xl' : 'text-[13px]'}`}>
            {product.name}
          </p>
          <div className="mt-1.5 flex items-center gap-2">
            {hasSale ? (
              <>
                <span className={`text-white font-bold ${large ? 'text-base' : 'text-[13px]'}`}>
                  {formatPrice(product.sale_price!)}
                </span>
                <span className="text-white/40 line-through text-[11px]">
                  {formatPrice(product.price)}
                </span>
              </>
            ) : (
              <span className={`text-white font-bold ${large ? 'text-base' : 'text-[13px]'}`}>
                {formatPrice(product.price)}
              </span>
            )}
          </div>
        </div>
      </div>
    </Link>
  )
}

/* ── 구분선 + 에디토리얼 카피 ──────────────────────────────────────────────── */
function EditorialBand() {
  return (
    <section className="bg-[#f7f7f5] py-16 px-4">
      <ScrollReveal y={20} duration={0.5}>
        <div className="mx-auto max-w-screen-xl">
          <div className="grid md:grid-cols-2 gap-8 items-center">
            <div>
              <p className="text-[11px] tracking-[0.25em] text-gray-400 uppercase mb-4">Our Curation</p>
              <h2 className="text-3xl md:text-4xl font-bold tracking-tight leading-tight editorial-heading">
                모든 취향을<br />
                존중합니다.
              </h2>
            </div>
            <p className="text-[15px] text-gray-500 leading-relaxed">
              zslab은 단순한 쇼핑몰이 아닙니다.<br />
              패션, 뷰티, 라이프스타일의 경계를 넘나들며
              당신의 일상에 영감을 더하는 편집숍입니다.
              지금 이 계절, 가장 필요한 것들을 큐레이션했습니다.
            </p>
          </div>
        </div>
      </ScrollReveal>
    </section>
  )
}

/* ── 신상품 — 가로 스크롤 ──────────────────────────────────────────────────── */
async function NewArrivals() {
  const products = await fetchProducts({ sort: 'latest', per_page: 10 })

  if (products.length === 0) return null

  return (
    <section className="py-16">
      <ScrollReveal className="mx-auto max-w-screen-xl px-4" y={16} duration={0.4}>
        <div className="flex items-end justify-between mb-8">
          <div>
            <p className="text-[11px] text-gray-400 tracking-[0.2em] uppercase mb-1">New Arrivals</p>
            <h2 className="text-2xl font-bold tracking-tight">신상품</h2>
          </div>
          <Link
            href="/products?sort=latest"
            className="text-[12px] text-gray-400 hover:text-[#111] transition-colors link-underline"
          >
            더보기
          </Link>
        </div>
      </ScrollReveal>

      {/* 가로 스크롤 */}
      <div className="px-4 lg:px-[calc((100vw-1280px)/2+1rem)]">
        <StaggerList
          className="flex gap-3 overflow-x-auto scrollbar-none pb-2"
          stagger={0.06}
          y={16}
          duration={0.4}
        >
          {products.map((product, i) => (
            <div key={product.id} className="flex-shrink-0 w-[160px] sm:w-[200px] lg:w-[220px]">
              <ProductCard product={product} ratio="portrait" priority={i < 3} />
            </div>
          ))}
        </StaggerList>
      </div>
    </section>
  )
}

/* ── 인기 상품 — 2×4 그리드 ────────────────────────────────────────────────── */
async function PopularProducts() {
  const products = await fetchProducts({ sort: 'popular', per_page: 8 })

  if (products.length === 0) return null

  return (
    <section className="py-16 bg-white">
      <div className="mx-auto max-w-screen-xl px-4">
        <ScrollReveal y={16} duration={0.4}>
          <div className="flex items-end justify-between mb-8">
            <div>
              <p className="text-[11px] text-gray-400 tracking-[0.2em] uppercase mb-1">Best Sellers</p>
              <h2 className="text-2xl font-bold tracking-tight">인기 상품</h2>
            </div>
            <Link
              href="/products?sort=popular"
              className="text-[12px] text-gray-400 hover:text-[#111] transition-colors link-underline"
            >
              더보기
            </Link>
          </div>
        </ScrollReveal>

        <StaggerList
          className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-10"
          stagger={0.07}
          y={20}
          duration={0.4}
        >
          {products.map((product, i) => (
            <ProductCard key={product.id} product={product} ratio="portrait" priority={i < 4} />
          ))}
        </StaggerList>
      </div>
    </section>
  )
}

/* ── 스켈레톤 ──────────────────────────────────────────────────────────────── */
function FeedSkeleton() {
  return (
    <div className="mx-auto max-w-screen-xl px-4 py-16">
      <div className="h-5 w-24 bg-gray-100 rounded animate-pulse mb-8" />
      <div className="grid grid-cols-12 gap-3">
        <div className="col-span-7 aspect-[3/4] bg-gray-100 rounded animate-pulse" />
        <div className="col-span-5 grid grid-rows-2 gap-3">
          <div className="aspect-[3/4] bg-gray-100 rounded animate-pulse" />
          <div className="aspect-[3/4] bg-gray-100 rounded animate-pulse" />
        </div>
      </div>
    </div>
  )
}

function ScrollSkeleton() {
  return (
    <div className="py-16 px-4">
      <div className="max-w-screen-xl mx-auto">
        <div className="h-5 w-20 bg-gray-100 rounded animate-pulse mb-8" />
        <div className="flex gap-3 overflow-hidden">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i} className="flex-shrink-0 w-[200px]">
              <div className="aspect-[3/4] bg-gray-100 rounded animate-pulse" />
              <div className="mt-3 space-y-2">
                <div className="h-3 bg-gray-100 rounded animate-pulse w-2/3" />
                <div className="h-3 bg-gray-100 rounded animate-pulse" />
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}

/* ── 홈 페이지 ─────────────────────────────────────────────────────────────── */
export default function HomePage() {
  return (
    <div className="min-h-screen">
      <HeroBanner />
      <CategoryQuickMenu />
      <Suspense fallback={<FeedSkeleton />}>
        <EditorialFeed />
      </Suspense>
      <EditorialBand />
      <Suspense fallback={<ScrollSkeleton />}>
        <NewArrivals />
      </Suspense>
      <Suspense fallback={<ScrollSkeleton />}>
        <PopularProducts />
      </Suspense>
      <RecommendationSection />
    </div>
  )
}
