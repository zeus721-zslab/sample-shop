import { formatDiscount, formatPrice } from '@/lib/format'
import type { Product } from '@/types'
import Image from 'next/image'
import Link from 'next/link'

interface Props {
  product: Product
  /** 이미지 비율 — 기본 4:5 */
  ratio?: 'square' | 'portrait' | 'landscape'
  priority?: boolean
}

const RATIO_CLASS = {
  square:    'aspect-square',
  portrait:  'aspect-[4/5]',
  landscape: 'aspect-[3/2]',
}

export default function ProductCard({ product, ratio = 'portrait', priority = false }: Props) {
  const hasSale = product.sale_price !== null && product.sale_price < product.price
  const isSoldout = product.status === 'soldout'

  return (
    <Link href={`/products/${product.slug}`} className="group block">
      {/* ── 이미지 ── */}
      <div className={`relative ${RATIO_CLASS[ratio]} bg-gray-50 overflow-hidden product-img-wrap`}>
        <Image
          src={product.images[0] ?? `https://picsum.photos/seed/${product.slug}/600/750`}
          alt={product.name}
          fill
          priority={priority}
          className="object-cover"
          sizes="(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 25vw"
        />

        {/* 품절 오버레이 */}
        {isSoldout && (
          <div className="absolute inset-0 bg-white/60 flex items-center justify-center">
            <span className="text-[11px] font-medium tracking-[0.2em] text-gray-500 uppercase">Sold Out</span>
          </div>
        )}

        {/* 할인율 뱃지 */}
        {hasSale && product.discount_rate >= 10 && !isSoldout && (
          <span className="absolute top-2 left-2 bg-[#111] text-white text-[10px] font-bold px-1.5 py-0.5 tracking-wide">
            {formatDiscount(product.discount_rate)}
          </span>
        )}
      </div>

      {/* ── 정보 ── */}
      <div className="mt-3 space-y-0.5">
        {product.category && (
          <p className="text-[11px] text-gray-400 tracking-wider uppercase truncate">
            {product.category.name}
          </p>
        )}
        <p className="text-[13px] text-[#111] leading-snug line-clamp-2 font-medium">
          {product.name}
        </p>
        <div className="flex items-baseline gap-1.5 pt-0.5">
          {hasSale ? (
            <>
              <span className="text-[13px] font-bold text-[#111]">{formatPrice(product.sale_price!)}</span>
              <span className="text-[11px] text-gray-300 line-through">{formatPrice(product.price)}</span>
            </>
          ) : (
            <span className="text-[13px] font-bold text-[#111]">{formatPrice(product.price)}</span>
          )}
        </div>
        {product.order_count > 0 && (
          <p className="text-[11px] text-gray-400">
            {product.order_count.toLocaleString()}개 판매
          </p>
        )}
      </div>
    </Link>
  )
}
