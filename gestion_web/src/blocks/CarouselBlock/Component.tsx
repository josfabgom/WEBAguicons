'use client'
import React, { useCallback, useEffect, useState } from 'react'

import { useHoverScroll } from '@/hooks/useHoverScroll'
import { mediaSrc } from '@/utilities/mediaSrc'

export const CarouselBlock: React.FC<any> = ({ title, images }) => {
  const { ref, hovering, handlers } = useHoverScroll(120)
  const slides = (images || []).filter((i: any) => i.image && typeof i.image === 'object')
  const [open, setOpen] = useState<number | null>(null)

  const go = useCallback(
    (d: number) => setOpen((o) => (o === null ? o : (o + d + slides.length) % slides.length)),
    [slides.length],
  )

  useEffect(() => {
    if (open === null) return
    const key = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setOpen(null)
      if (e.key === 'ArrowRight') go(1)
      if (e.key === 'ArrowLeft') go(-1)
    }
    document.body.style.overflow = 'hidden'
    window.addEventListener('keydown', key)
    return () => {
      document.body.style.overflow = ''
      window.removeEventListener('keydown', key)
    }
  }, [open, go])

  const btn =
    'absolute z-10 size-11 rounded-full bg-gold text-white text-2xl leading-none shadow focus-visible:outline focus-visible:outline-2 focus-visible:outline-white'

  return (
    <div className="container mx-auto py-12">
      {title && (
        <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
      )}
      <div
        ref={ref}
        {...handlers}
        className={`flex overflow-x-auto gap-4 pb-4 select-none cursor-grab active:cursor-grabbing ${hovering ? '' : 'snap-x snap-mandatory scroll-smooth'}`}
      >
        {slides.map((item: any, index: number) => (
          <button
            type="button"
            key={index}
            onClick={() => setOpen(index)}
            aria-label={`Ampliar imagen ${index + 1}`}
            className="shrink-0 w-full sm:w-[80%] md:w-[60%] snap-center cursor-zoom-in"
          >
            <img
              src={mediaSrc(item.image, 'large')}
              alt={item.image.alt || `${title || 'Vista'} ${index + 1}`}
              loading={index < 2 ? 'eager' : 'lazy'}
              decoding="async"
              className="w-full h-auto object-cover rounded-md shadow-lg"
            />
          </button>
        ))}
      </div>

      {open !== null && slides[open] && (
        <div
          role="dialog"
          aria-modal="true"
          aria-label="Galería de imágenes"
          className="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4"
          onClick={() => setOpen(null)}
        >
          <button
            type="button"
            aria-label="Cerrar"
            className={`${btn} top-4 right-4`}
            onClick={() => setOpen(null)}
          >
            ×
          </button>
          <button
            type="button"
            aria-label="Anterior"
            className={`${btn} left-4 top-1/2 -translate-y-1/2`}
            onClick={(e) => {
              e.stopPropagation()
              go(-1)
            }}
          >
            ‹
          </button>
          <img
            src={mediaSrc(slides[open].image, 'xlarge')}
            alt={slides[open].image.alt || `Imagen ${open + 1}`}
            className="max-h-[90vh] max-w-[92vw] object-contain"
            onClick={(e) => e.stopPropagation()}
          />
          <button
            type="button"
            aria-label="Siguiente"
            className={`${btn} right-4 top-1/2 -translate-y-1/2`}
            onClick={(e) => {
              e.stopPropagation()
              go(1)
            }}
          >
            ›
          </button>
          <span className="absolute bottom-4 text-white/80 text-sm">
            {open + 1} / {slides.length}
          </span>
        </div>
      )}
    </div>
  )
}
