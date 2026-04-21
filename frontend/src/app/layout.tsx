import AuthProvider from '@/components/AuthProvider'
import Header from '@/components/Header'
import type { Metadata } from 'next'
import { Inter, Noto_Sans_KR } from 'next/font/google'
import Link from 'next/link'
import './globals.css'

const inter = Inter({
  variable: '--font-inter',
  subsets: ['latin'],
  display: 'swap',
})

const notoSansKR = Noto_Sans_KR({
  variable: '--font-noto-kr',
  subsets: ['latin'],
  weight: ['300', '400', '500', '700'],
  display: 'swap',
})

export const metadata: Metadata = {
  title: { default: 'zslab', template: '%s — zslab' },
  description: '패션부터 라이프스타일까지, 취향을 발견하는 공간',
  openGraph: {
    siteName: 'zslab',
    type: 'website',
  },
}

const FOOTER_LINKS = {
  '쇼핑': [
    { label: '신상품', href: '/products?sort=latest' },
    { label: '인기상품', href: '/products?sort=popular' },
    { label: '패션/의류', href: '/products?category=fashion' },
    { label: '전자제품', href: '/products?category=electronics' },
  ],
  '고객센터': [
    { label: '공지사항', href: '#' },
    { label: '자주 묻는 질문', href: '#' },
    { label: '1:1 문의', href: '#' },
    { label: '배송 안내', href: '#' },
  ],
  '회사': [
    { label: '브랜드 소개', href: '#' },
    { label: '이용약관', href: '#' },
    { label: '개인정보처리방침', href: '#' },
  ],
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html
      lang="ko"
      className={`${inter.variable} ${notoSansKR.variable} h-full`}
      style={{ fontFamily: 'var(--font-noto-kr), var(--font-inter), sans-serif' }}
    >
      <body className="min-h-full flex flex-col bg-white text-[#111]">
        <AuthProvider>
          <Header />
          <main className="flex-1">{children}</main>

          {/* ── Footer ───────────────────────────────────── */}
          <footer className="border-t border-gray-100 mt-20">
            <div className="mx-auto max-w-screen-xl px-4 py-14">
              <div className="grid grid-cols-2 md:grid-cols-4 gap-10">
                {/* Brand */}
                <div className="col-span-2 md:col-span-1">
                  <Link href="/" className="text-xl font-bold tracking-tight">
                    zslab
                  </Link>
                  <p className="mt-3 text-xs text-gray-400 leading-relaxed">
                    패션부터 라이프스타일까지
                    <br />
                    취향을 발견하는 공간
                  </p>
                </div>

                {/* Link groups */}
                {Object.entries(FOOTER_LINKS).map(([title, links]) => (
                  <div key={title}>
                    <p className="text-xs font-semibold text-gray-900 mb-4 tracking-wider uppercase">
                      {title}
                    </p>
                    <ul className="space-y-2.5">
                      {links.map((link) => (
                        <li key={link.label}>
                          <Link
                            href={link.href}
                            className="text-xs text-gray-500 hover:text-gray-900 transition-colors"
                          >
                            {link.label}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>

              {/* Bottom bar */}
              <div className="mt-10 pt-6 border-t border-gray-50 flex flex-col sm:flex-row justify-between gap-2">
                <p className="text-[11px] text-gray-300">© 2026 zslab. All rights reserved.</p>
                <p className="text-[11px] text-gray-300">zslab Inc. · co.kr.zslab@gmail.com</p>
              </div>
            </div>
          </footer>
        </AuthProvider>
      </body>
    </html>
  )
}
