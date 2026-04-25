'use client'

import { useEffect } from 'react'

export default function SentryInit() {
  useEffect(() => {
    const dsn = process.env.NEXT_PUBLIC_SENTRY_DSN
    if (!dsn || process.env.NODE_ENV !== 'production') return

    import('@sentry/nextjs').then((Sentry) => {
      if (Sentry.isInitialized()) return
      Sentry.init({
        dsn,
        environment: process.env.NODE_ENV,
        tracesSampleRate: 0.1,
        replaysOnErrorSampleRate: 1.0,
      })
    })
  }, [])

  return null
}
