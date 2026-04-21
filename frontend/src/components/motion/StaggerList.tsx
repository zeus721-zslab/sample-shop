'use client'

import { type Variants, motion, useInView } from 'framer-motion'
import { useRef } from 'react'

interface Props {
  children: React.ReactNode[]
  className?: string
  stagger?: number
  y?: number
  duration?: number
  once?: boolean
}

const container = (stagger: number): Variants => ({
  hidden: {},
  show: { transition: { staggerChildren: stagger } },
})

const item = (y: number, duration: number): Variants => ({
  hidden: { opacity: 0, y },
  show: { opacity: 1, y: 0, transition: { duration, ease: [0.25, 0.46, 0.45, 0.94] } },
})

export default function StaggerList({
  children,
  className,
  stagger = 0.07,
  y = 20,
  duration = 0.4,
  once = true,
}: Props) {
  const ref = useRef<HTMLDivElement>(null)
  const inView = useInView(ref, { once, margin: '-40px 0px' })

  return (
    <motion.div
      ref={ref}
      className={className}
      variants={container(stagger)}
      initial="hidden"
      animate={inView ? 'show' : 'hidden'}
    >
      {children.map((child, i) => (
        <motion.div key={i} variants={item(y, duration)}>
          {child}
        </motion.div>
      ))}
    </motion.div>
  )
}
