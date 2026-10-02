import { Playfair_Display } from 'next/font/google'
import React from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'
import { GoldLogo } from '@/components/GoldLogo'

const script = Playfair_Display({ subsets: ['latin'], weight: ['500', '700'], style: ['italic'] })

export const ProjectIntroBlock: React.FC<any> = ({ logo, name, text, amenities, scriptTitle }) => {
  return (
    <section className="bg-black text-white pt-10 pb-12">
      <div className="container max-w-4xl text-center">
        <GoldLogo className="mx-auto w-24 md:w-28 mb-10" />
        {logo && typeof logo === 'object' ? (
          <img src={mediaSrc(logo, 'medium')} alt={logo.alt || name} className="mx-auto h-16 object-contain mb-10" />
        ) : (
          <h2
            className={
              scriptTitle
                ? `${script.className} text-6xl md:text-7xl mb-10 leading-none`
                : 'text-4xl md:text-5xl tracking-[0.35em] uppercase mb-10 font-normal'
            }
          >
            {name}
          </h2>
        )}
        <div className="mx-auto max-w-3xl text-left text-sm md:text-base leading-relaxed space-y-0">
          {String(text || '')
            .split(/\n+/)
            .map((p, i) => (
              <p key={i} className="indent-8">
                {p}
              </p>
            ))}
        </div>
        {amenities?.length > 0 && (
          <ul className="mt-8 flex flex-wrap justify-center gap-x-8 gap-y-2 text-sm md:text-base">
            {amenities.map((a: any, i: number) => (
              <li key={i} className="flex items-center gap-2">
                <span className="inline-block size-2.5 rounded-full bg-gold" />
                {a.label}
              </li>
            ))}
          </ul>
        )}
      </div>
    </section>
  )
}
