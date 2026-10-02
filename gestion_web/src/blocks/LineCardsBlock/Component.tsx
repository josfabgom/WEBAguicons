'use client'
import React, { useCallback } from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'
import { CMSLink } from '@/components/Link'
import { useHoverScroll } from '@/hooks/useHoverScroll'

export const LineCardsBlock: React.FC<any> = ({ title, cards }) => {
  const { ref: track, hovering, handlers } = useHoverScroll(70)

  const scrollByCard = useCallback((dir: 1 | -1) => {
    const el = track.current
    if (!el) return
    const step = (el.firstElementChild as HTMLElement | null)?.offsetWidth ?? el.clientWidth
    const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4
    if (dir === 1 && atEnd) el.scrollTo({ left: 0, behavior: 'smooth' })
    else if (dir === -1 && el.scrollLeft <= 4) el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' })
    else el.scrollBy({ left: dir * (step + 16), behavior: 'smooth' })
  }, [track])


  const arrow =
    'absolute top-1/2 -translate-y-1/2 z-10 size-10 rounded-full bg-gold text-white text-xl leading-none shadow hover:opacity-90'

  return (
    <div className="container py-12">
      {title && (
        <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
      )}
      <div
        className="relative"
        {...handlers}
      >
        <button aria-label="Anterior" className={`${arrow} left-2`} onClick={() => scrollByCard(-1)}>
          ‹
        </button>
        <button aria-label="Siguiente" className={`${arrow} right-2`} onClick={() => scrollByCard(1)}>
          ›
        </button>
        <div
          ref={track}
          className={`flex gap-4 overflow-x-auto select-none cursor-grab active:cursor-grabbing ${hovering ? '' : 'snap-x snap-mandatory scroll-smooth'} [scrollbar-width:none] [&::-webkit-scrollbar]:hidden`}
        >
          {cards?.map((card: any, i: number) => {
            const inner = (
              <div className="group relative bg-black aspect-[1/2] flex items-center justify-center overflow-hidden">
                <span className="absolute inset-0 z-10 flex items-center justify-center bg-black/55 text-white text-xl font-bold tracking-[0.3em] uppercase opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                  {card.name}
                </span>
                {card.logo && typeof card.logo === 'object' ? (
                  <img
                    src={mediaSrc(card.logo, 'medium')}
                    loading="lazy"
                    decoding="async"
                    alt={card.logo.alt || card.name}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <span className="text-white tracking-[0.3em] uppercase text-sm">{card.name}</span>
                )}
              </div>
            )
            const cls = 'shrink-0 snap-start w-[75%] sm:w-[45%] md:w-[30%] lg:w-[23%]'
            return card.link?.url || card.link?.reference ? (
              <CMSLink key={i} {...card.link} appearance="inline" className={`block ${cls}`}>
                {inner}
              </CMSLink>
            ) : (
              <div key={i} className={cls}>
                {inner}
              </div>
            )
          })}
        </div>
      </div>
    </div>
  )
}
