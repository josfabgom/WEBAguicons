'use client'
import Script from 'next/script'
import React, { useEffect, useState } from 'react'

const KEY = 'aguicons-cookies'

/**
 * Google Analytics 4 con consentimiento: solo se carga si hay NEXT_PUBLIC_GA_ID y el visitante acepta.
 * Sin ID configurado no muestra aviso ni carga nada.
 */
export const Analytics: React.FC = () => {
  const id = process.env.NEXT_PUBLIC_GA_ID
  const [choice, setChoice] = useState<'granted' | 'denied' | null | undefined>(undefined)

  useEffect(() => {
    try {
      setChoice((localStorage.getItem(KEY) as any) || null)
    } catch {
      setChoice(null)
    }
  }, [])

  if (!id || choice === undefined) return null

  const save = (v: 'granted' | 'denied') => {
    try {
      localStorage.setItem(KEY, v)
    } catch {}
    setChoice(v)
  }

  return (
    <>
      {choice === 'granted' && (
        <>
          <Script src={`https://www.googletagmanager.com/gtag/js?id=${id}`} strategy="afterInteractive" />
          <Script id="ga-init" strategy="afterInteractive">{`
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            window.gtag = gtag;
            gtag('js', new Date());
            gtag('config', '${id}', { anonymize_ip: true });
          `}</Script>
        </>
      )}
      {choice === null && (
        <div
          role="dialog"
          aria-label="Aviso de cookies"
          className="fixed bottom-0 inset-x-0 z-[90] bg-black text-white text-sm p-4 flex flex-col sm:flex-row gap-3 items-center justify-between"
        >
          <p className="max-w-3xl">
            Usamos cookies de analítica para mejorar el sitio. Más información en nuestra{' '}
            <a href="/politica-de-privacidad" className="underline">
              política de privacidad
            </a>
            .
          </p>
          <div className="flex gap-2 shrink-0">
            <button
              type="button"
              onClick={() => save('denied')}
              className="border border-white/60 rounded-lg px-4 py-2"
            >
              Rechazar
            </button>
            <button
              type="button"
              onClick={() => save('granted')}
              className="bg-gold text-white font-bold rounded-lg px-4 py-2"
            >
              Aceptar
            </button>
          </div>
        </div>
      )}
    </>
  )
}
