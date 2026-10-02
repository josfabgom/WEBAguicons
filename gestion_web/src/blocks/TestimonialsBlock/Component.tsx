import React from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'

export const TestimonialsBlock: React.FC<any> = ({ title, items }) => {
  if (!items?.length) return null
  return (
    <section className="container max-w-5xl">
      {title && (
        <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
      )}
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {items.map((t: any, i: number) => (
          <figure key={i} className="border border-gold rounded-xl p-6 flex flex-col gap-4">
            <blockquote className="text-sm leading-relaxed">“{t.quote}”</blockquote>
            <figcaption className="mt-auto flex items-center gap-3">
              {t.photo && typeof t.photo === 'object' && (
                <img
                  src={mediaSrc(t.photo, 'thumbnail')}
                  alt={t.name}
                  className="size-10 rounded-full object-cover"
                />
              )}
              <span className="text-sm">
                <strong className="block">{t.name}</strong>
                {t.role && <span className="text-muted-foreground">{t.role}</span>}
              </span>
            </figcaption>
          </figure>
        ))}
      </div>
    </section>
  )
}
