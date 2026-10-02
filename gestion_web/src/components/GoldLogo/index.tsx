import React from 'react'

/** Logo oficial de Aguicons (archivos transparentes en /public/logos). */
export const GoldLogo: React.FC<{
  className?: string
  /** 'full' = isotipo + AGUICONS, 'iso' = solo isotipo */
  variant?: 'full' | 'iso'
  tone?: 'oro' | 'blanco'
}> = ({ className = 'w-20 md:w-24', variant = 'full', tone = 'oro' }) => (
  <img
    src={`/logos/aguicons${variant === 'iso' ? '-isotipo' : ''}-${tone}.png`}
    alt="Aguicons"
    width={700}
    height={variant === 'iso' ? 606 : 644}
    className={`h-auto ${className}`}
  />
)
