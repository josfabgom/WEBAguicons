import React from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'
import { CMSLink } from '@/components/Link'

export const FeaturedCardsBlock: React.FC<any> = ({ title, cards }) => {
  return (
    <section className="bg-white py-12">
      <div className="container">
        {title && (
          <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
        )}
        <div className="flex flex-wrap justify-center gap-6">
          {cards?.map((card: any, i: number) => {
            const inner = (
              <div className="relative w-64 sm:w-72 aspect-[3/4] bg-white border border-gold overflow-hidden flex items-center justify-center transition-transform hover:scale-[1.02]">
                {card.image && typeof card.image === 'object' ? (
                  <img
                    src={mediaSrc(card.image, 'medium')}
                    loading="lazy"
                    decoding="async"
                    alt={card.image.alt || card.name}
                    className="absolute inset-0 w-full h-full object-cover"
                  />
                ) : null}
                <span className="relative z-10 bg-black/60 text-white font-bold uppercase tracking-widest px-3 py-1 text-sm">
                  {card.name}
                </span>
                {card.badgeTitle && (
                  <div className="absolute bottom-0 right-0 bg-gold text-white text-center text-[10px] leading-tight px-3 py-1 uppercase">
                    <div className="font-bold">{card.badgeTitle}</div>
                    {card.badgeText && <div className="normal-case">{card.badgeText}</div>}
                  </div>
                )}
              </div>
            )
            return card.link?.url || card.link?.reference ? (
              <CMSLink key={i} {...card.link} appearance="inline" className="block">
                {inner}
              </CMSLink>
            ) : (
              <div key={i}>{inner}</div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
