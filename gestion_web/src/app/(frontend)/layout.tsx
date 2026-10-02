import type { Metadata } from 'next'

import { cn } from '@/utilities/ui'
import { GeistMono } from 'geist/font/mono'
import { Poppins } from 'next/font/google'
import React from 'react'

import { Analytics } from '@/components/Analytics'
import { AdminBar } from '@/components/AdminBar'
import { Footer } from '@/Footer/Component'
import { Header } from '@/Header/Component'
import { Providers } from '@/providers'
import { InitTheme } from '@/providers/Theme/InitTheme'
import { mergeOpenGraph } from '@/utilities/mergeOpenGraph'
import { draftMode } from 'next/headers'

import './globals.css'
import { getServerSideURL } from '@/utilities/getURL'

// Gotham no está en Google Fonts: Poppins (geométrica, similar) la reemplaza (Regular y Bold).
const gotham = Poppins({
  subsets: ['latin'],
  weight: ['400', '700'],
  variable: '--font-gotham',
  display: 'swap',
})

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const { isEnabled } = await draftMode()

  return (
    <html className={cn(gotham.variable, GeistMono.variable)} lang="es" suppressHydrationWarning>
      <head>
        <InitTheme />
        <link href="/logos/aguicons-isotipo-oro.png" rel="icon" type="image/png" />
      </head>
      <body>
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify({
              '@context': 'https://schema.org',
              '@type': 'Organization',
              name: 'Aguicons',
              url: getServerSideURL(),
              email: 'info@aguicons.com',
              telephone: '+54 9 3764 630673',
              address: {
                '@type': 'PostalAddress',
                streetAddress: 'Av. Antártida Argentina 876',
                addressLocality: 'Posadas',
                addressRegion: 'Misiones',
                addressCountry: 'AR',
              },
            }),
          }}
        />
        <a
          href="#contenido"
          className="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[200] focus:bg-gold focus:text-white focus:px-4 focus:py-2"
        >
          Saltar al contenido
        </a>
        <Providers>
          <AdminBar
            adminBarProps={{
              preview: isEnabled,
            }}
          />

          <Header />
          <main id="contenido">{children}</main>
          <Footer />
          <Analytics />
        </Providers>
      </body>
    </html>
  )
}

export const metadata: Metadata = {
  metadataBase: new URL(getServerSideURL()),
  openGraph: mergeOpenGraph(),
  twitter: {
    card: 'summary_large_image',
    creator: '@payloadcms',
  },
}
