import AddToCartButton from '@/components/AddToCartButton'
import ReviewSection from '@/components/ReviewSection'
import WishlistButton from '@/components/WishlistButton'
import { productApi, reviewApi } from '@/lib/api'
import { formatDiscount, formatPrice } from '@/lib/format'
import type { Metadata } from 'next'
import Image from 'next/image'
import Link from 'next/link'
import { notFound } from 'next/navigation'

interface Props {
  params: Promise<{ slug: string }>
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params
  try {
    const product = await productApi.get(slug)
    return {
      title: product.name,
      description: product.description ?? undefined,
      openGraph: { images: product.images[0] ? [product.images[0]] : [] },
    }
  } catch {
    return { title: '상품 상세' }
  }
}

export default async function ProductDetailPage({ params }: Props) {
  const { slug } = await params

  let product: Awaited<ReturnType<typeof productApi.get>>
  try {
    product = await productApi.get(slug)
  } catch {
    notFound()
  }

  // 리뷰 초기 데이터 (서버 사이드)
  const reviewsRes = await reviewApi.list(product.id).catch(() => ({ data: [], total: 0, current_page: 1, last_page: 1, per_page: 10 }))

  const hasSale = product.sale_price !== null && product.sale_price < product.price
  const isSoldout = product.status === 'soldout'

  return (
    <div className="mx-auto max-w-screen-xl px-4 py-10">
      {/* 브레드크럼 */}
      <nav className="flex items-center gap-2 text-xs text-gray-400 mb-8">
        <Link href="/" className="hover:text-gray-700">홈</Link>
        <span>/</span>
        <Link href="/products" className="hover:text-gray-700">상품</Link>
        {product.category && (
          <>
            <span>/</span>
            <Link href={`/category/${product.category.slug}`} className="hover:text-gray-700">
              {product.category.name}
            </Link>
          </>
        )}
        <span>/</span>
        <span className="text-gray-600 truncate max-w-[160px]">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">
        {/* ── 이미지 ───────────────────────────────────────── */}
        <div className="space-y-3">
          <div className="relative aspect-[4/5] bg-gray-50 overflow-hidden">
            <Image
              src={product.images[0] ?? `https://picsum.photos/seed/${product.slug}/600/750`}
              alt={product.name}
              fill
              priority
              className="object-cover"
              sizes="(max-width: 768px) 100vw, 50vw"
            />
            {isSoldout && (
              <div className="absolute inset-0 bg-white/60 flex items-center justify-center">
                <span className="text-gray-500 text-lg font-medium tracking-[0.2em]">SOLD OUT</span>
              </div>
            )}
            {hasSale && product.discount_rate >= 10 && (
              <span className="absolute top-3 left-3 bg-[#111] text-white text-xs font-bold px-2 py-1 tracking-wide">
                {formatDiscount(product.discount_rate)}
              </span>
            )}
          </div>

          {product.images.length > 1 && (
            <div className="flex gap-2">
              {product.images.slice(0, 5).map((img, i) => (
                <div key={i} className="relative w-16 h-16 bg-gray-50 overflow-hidden flex-shrink-0 border border-gray-100">
                  <Image src={img} alt="" fill className="object-cover" sizes="64px" />
                </div>
              ))}
            </div>
          )}
        </div>

        {/* ── 상품 정보 ─────────────────────────────────────── */}
        <div className="flex flex-col gap-4">
          {product.category && (
            <Link href={`/category/${product.category.slug}`} className="text-[11px] text-gray-400 uppercase tracking-[0.2em] hover:text-gray-700">
              {product.category.name}
            </Link>
          )}

          <h1 className="text-2xl font-bold leading-snug tracking-tight">{product.name}</h1>

          {product.rating_avg > 0 && (
            <div className="flex items-center gap-2 text-sm text-gray-500">
              <span className="text-amber-400">★</span>
              <span className="font-medium">{product.rating_avg.toFixed(1)}</span>
              <span className="text-gray-200">·</span>
              <span>{product.order_count.toLocaleString()}개 판매</span>
            </div>
          )}

          {/* 가격 */}
          <div className="border-t border-b py-4 my-2">
            {hasSale ? (
              <div className="flex items-baseline gap-3">
                <span className="text-[28px] font-bold text-[#111]">{formatPrice(product.sale_price!)}</span>
                <span className="text-gray-300 line-through text-base">{formatPrice(product.price)}</span>
                <span className="text-red-500 text-sm font-bold">{formatDiscount(product.discount_rate)} 할인</span>
              </div>
            ) : (
              <span className="text-[28px] font-bold">{formatPrice(product.price)}</span>
            )}
          </div>

          {product.stock > 0 && product.stock <= 10 && (
            <p className="text-sm text-orange-500 font-medium">재고 {product.stock}개 남음</p>
          )}

          {/* 장바구니 + 위시리스트 */}
          <div className="flex gap-2 mt-2">
            <div className="flex-1">
              <AddToCartButton
                productId={product.id}
                productName={product.name}
                isSoldout={isSoldout}
                maxStock={product.stock}
              />
            </div>
            <WishlistButton productId={product.id} />
          </div>

          {/* 배송 안내 */}
          <div className="bg-gray-50 rounded-lg p-4 text-xs text-gray-500 space-y-1.5 mt-2">
            <div className="flex justify-between">
              <span className="text-gray-400">배송비</span>
              <span className="font-medium text-gray-700">3,000원 (50,000원 이상 무료)</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-400">배송기간</span>
              <span className="font-medium text-gray-700">2~4 영업일</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-400">반품/교환</span>
              <span className="font-medium text-gray-700">수령 후 7일 이내</span>
            </div>
          </div>

          {/* 상품 설명 */}
          {product.description && (
            <div className="border-t pt-5 mt-2">
              <h2 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">상품 설명</h2>
              <p className="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{product.description}</p>
            </div>
          )}
        </div>
      </div>

      {/* ── 리뷰 섹션 ──────────────────────────────────────── */}
      <ReviewSection
        productId={product.id}
        initialReviews={reviewsRes.data}
        initialTotal={reviewsRes.total}
      />
    </div>
  )
}
