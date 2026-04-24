'use client'

import { motion } from 'framer-motion'
import Link from 'next/link'

export default function TermsPage() {
  return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center px-4 py-20">
      {/* 브레드크럼 */}
      <nav className="absolute top-20 left-4 sm:left-8 text-xs text-gray-400 flex items-center gap-1.5">
        <Link href="/" className="hover:text-gray-700 transition-colors">홈</Link>
        <span>/</span>
        <span className="text-gray-700">이용약관</span>
      </nav>

      <motion.div
        initial={{ opacity: 0, y: 24 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, ease: 'easeOut' }}
        className="text-center"
      >
        {/* 아이콘 */}
        <motion.div
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          transition={{ duration: 0.45, delay: 0.1 }}
          className="text-6xl mb-8"
        >
          📋
        </motion.div>

        <Link href="/" className="block mb-8">
          <span className="text-2xl font-black tracking-tight">zslab</span>
        </Link>

        <motion.h1
          initial={{ opacity: 0, y: 8 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, delay: 0.2 }}
          className="text-3xl font-bold tracking-tight mb-3"
        >
          서비스 준비 중입니다
        </motion.h1>

        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.4, delay: 0.3 }}
          className="text-gray-400 text-sm mb-10 leading-relaxed"
        >
          이용약관 페이지를 준비 중입니다.
          <br />
          곧 더 나은 내용으로 찾아뵙겠습니다.
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 8 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, delay: 0.4 }}
        >
          <Link href="/">
            <motion.span
              whileHover={{ scale: 1.04, backgroundColor: '#111' }}
              whileTap={{ scale: 0.97 }}
              className="inline-block bg-black text-white text-sm font-medium px-8 py-3.5 rounded-full cursor-pointer transition-colors"
            >
              홈으로 돌아가기
            </motion.span>
          </Link>
        </motion.div>
      </motion.div>
    </div>
  )
}
