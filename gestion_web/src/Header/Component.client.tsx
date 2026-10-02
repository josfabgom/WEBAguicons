'use client'
import { useHeaderTheme } from '@/providers/HeaderTheme'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { MenuIcon, SearchIcon, XIcon } from 'lucide-react'
import React, { useEffect, useState } from 'react'

import type { Header } from '@/payload-types'

import { CMSLink } from '@/components/Link'
import { GoldLogo } from '@/components/GoldLogo'

interface HeaderClientProps {
  data: Header
}

const LINES = [
  { label: 'Baradise', url: '/baradise' },
  { label: 'Velerian', url: '/velerian' },
  { label: 'Benrow', url: '/benrow' },
  { label: 'Tier', url: '/tier' },
  { label: 'Alarif', url: '/alarif' },
]

export const HeaderClient: React.FC<HeaderClientProps> = ({ data }) => {
  /* Storing the value in a useState to avoid hydration errors */
  const [theme, setTheme] = useState<string | null>(null)
  const [open, setOpen] = useState(false)
  const { headerTheme, setHeaderTheme } = useHeaderTheme()
  const pathname = usePathname()
  const isHome = pathname === '/'
  const navItems = data?.navItems || []

  useEffect(() => {
    setHeaderTheme(null)
    setOpen(false)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname])

  useEffect(() => {
    if (headerTheme && headerTheme !== theme) setTheme(headerTheme)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [headerTheme])

  return (
    <header
      className={
        isHome
          ? 'absolute inset-x-0 top-0 z-30'
          : 'relative z-30 border-b border-border bg-background'
      }
      {...(theme ? { 'data-theme': theme } : {})}
    >
      <div className="container flex items-center justify-between py-3">
        {isHome ? (
          <span />
        ) : (
          <Link href="/" aria-label="Aguicons - Inicio">
            <GoldLogo variant="iso" className="w-12" />
          </Link>
        )}

        <nav aria-label="Principal" className="hidden md:flex gap-3 items-center">
          {navItems.map(({ link }, i) => (
            <CMSLink key={i} {...link} appearance="link" />
          ))}
          {!isHome && (
            <Link href="/search" className="focus-visible:outline focus-visible:outline-2">
              <span className="sr-only">Buscar</span>
              <SearchIcon className="w-5 text-primary" />
            </Link>
          )}
        </nav>

        <button
          type="button"
          className="md:hidden p-2 rounded focus-visible:outline focus-visible:outline-2"
          aria-label={open ? 'Cerrar menú' : 'Abrir menú'}
          aria-expanded={open}
          aria-controls="menu-movil"
          onClick={() => setOpen((o) => !o)}
        >
          {open ? <XIcon className="size-6" /> : <MenuIcon className="size-6" />}
        </button>
      </div>

      {open && (
        <nav
          id="menu-movil"
          aria-label="Menú móvil"
          className="md:hidden absolute inset-x-0 top-full bg-background border-b border-border shadow-lg"
        >
          <ul className="container py-4 flex flex-col gap-3">
            {navItems.map(({ link }, i) => (
              <li key={i}>
                <CMSLink {...link} appearance="link" />
              </li>
            ))}
            <li className="pt-2 border-t border-border">
              <span className="block text-xs font-bold uppercase tracking-widest text-gold mb-2">
                Líneas edilicias
              </span>
              <ul className="flex flex-wrap gap-2">
                {LINES.map((l) => (
                  <li key={l.url}>
                    <Link
                      href={l.url}
                      className="inline-block border border-gold rounded-lg px-3 py-1 text-sm"
                    >
                      {l.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </li>
          </ul>
        </nav>
      )}
    </header>
  )
}
