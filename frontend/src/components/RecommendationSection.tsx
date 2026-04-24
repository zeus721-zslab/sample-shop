'use client'

import ProductCard from '@/components/ProductCard'
import ScrollReveal from '@/components/motion/ScrollReveal'
import StaggerList from '@/components/motion/StaggerList'
import { recommendApi } from '@/lib/api'
import { useAuth } from '@/store/auth'
import type { Product } from '@/types'
import Link from 'next/link'
import { useEffect, useState } from 'react'

export default function RecommendationSection() {
  const { token } = useAuth()
  const [products, setProducts] = useState<Product[]>([])
  const [type, setType] = useState<'personalized' | 'popular'>('popular')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    recommendApi.get(token)
      .then((res) => {
        setProducts(res.products)
        setType(res.type)
      })
      .catch(() => setProducts([]))
      .finally(() => setLoading(false))
  }, [token])

  if (loading) {
    return (
      <section className="py-16 bg-[#f7f7f5]">
        <div className="mx-auto max-w-screen-xl px-4">
          <div className="h-5 w-32 bg-gray-200 rounded animate-pulse mb-8" />
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-10">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="animate-pulse">
                <div className="aspect-[3/4] bg-gray-200 rounded-lg mb-3" />
                <div className="h-3 bg-gray-200 rounded w-2/3 mb-2" />
                <div className="h-3 bg-gray-200 rounded w-1/2" />
              </div>
            ))}
          </div>
        </div>
      </section>
    )
  }

  if (products.length === 0) return null

  return (
    <section className="py-16 bg-[#f7f7f5]">
      <div className="mx-auto max-w-screen-xl px-4">
        <ScrollReveal y={16} duration={0.4}>
          <div className="flex items-end justify-between mb-8">
            <div>
              <p className="text-[11px] text-gray-400 tracking-[0.2em] uppercase mb-1">
                {type === 'personalized' ? 'For You' : 'Trending'}
              </p>
              <h2 className="text-2xl font-bold tracking-tight">
                {type === 'personalized' ? '맞춤 추천 상품' : '지금 인기 있는 상품'}
              </h2>
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
          {products.slice(0, 8).map((product, i) => (
            <ProductCard key={product.id} product={product} ratio="portrait" priority={i < 4} />
          ))}
        </StaggerList>
      </div>
    </section>
  )
}
