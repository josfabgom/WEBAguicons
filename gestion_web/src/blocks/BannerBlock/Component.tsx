import React from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'
import { CMSLink } from '@/components/Link'
import { GoldLogo } from '@/components/GoldLogo'

export const BannerBlock: React.FC<any> = ({ image, title, titleAccent, titleAfter, link }) => {
  if (!image || typeof image !== 'object') return null
  return (
    <section className="relative w-full overflow-hidden bg-white">
      <img
        src={mediaSrc(image, 'xlarge')}
        fetchPriority="high"
        alt={image.alt || 'Aguicons'}
        className="w-full h-[75vh] min-h-[460px] object-cover object-bottom"
      />
      <div className="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-white via-white/80 to-transparent" />
      <div className="absolute inset-x-0 top-4 flex justify-center">
        <GoldLogo />
      </div>
      {title && (
        <h1 className="absolute inset-x-0 top-[28%] text-center text-white font-bold uppercase tracking-wide text-2xl sm:text-4xl md:text-6xl px-2 drop-shadow-lg">
          {title}
          {titleAccent && <em className="mx-3 font-normal italic normal-case">{titleAccent}</em>}
          {titleAfter}
        </h1>
      )}
      {(link?.url || link?.reference) && (
        <div className="absolute bottom-6 right-6 border-2 border-white/90">
          <CMSLink
            {...link}
            appearance="inline"
            className="block bg-gold text-white text-lg leading-tight px-5 py-3 max-w-[11rem] hover:opacity-90"
          />
        </div>
      )}
    </section>
  )
}
