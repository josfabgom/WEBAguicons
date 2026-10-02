'use client'
import React, { useEffect, useRef, useState } from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'

/** Anima el número ("+5.000", "+500") desde 0 al entrar en pantalla. */
const CountUp: React.FC<{ value: string }> = ({ value }) => {
  const ref = useRef<HTMLSpanElement>(null)
  const m = value.match(/^(\D*)([\d.,]+)(\D*)$/)
  const target = m ? parseInt(m[2].replace(/[.,]/g, ''), 10) : NaN
  const [n, setN] = useState<number | null>(null)

  useEffect(() => {
    const el = ref.current
    if (!el || Number.isNaN(target)) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    setN(0)
    const io = new IntersectionObserver(([e]) => {
      if (!e.isIntersecting) return
      io.disconnect()
      const t0 = performance.now()
      const step = (now: number) => {
        const p = Math.min((now - t0) / 1400, 1)
        setN(Math.round(target * (1 - Math.pow(1 - p, 3))))
        if (p < 1) requestAnimationFrame(step)
      }
      requestAnimationFrame(step)
    })
    io.observe(el)
    return () => io.disconnect()
  }, [target])

  if (!m || n === null) return <span ref={ref}>{value}</span>
  return (
    <span ref={ref}>
      {m[1]}
      {n.toLocaleString('es-AR')}
      {m[3]}
    </span>
  )
}

export const StatsBlock: React.FC<any> = ({ stats }) => {
  return (
    <div className="container mx-auto py-12 bg-white">
      <h2 className="text-center font-bold text-xl tracking-widest uppercase mb-8">Estadísticas</h2>
      <div className="flex flex-col md:flex-row justify-center items-center gap-8 md:gap-16">
        {stats?.map((stat: any, index: number) => (
          <div key={index} className="flex flex-col items-center justify-center text-center">
            {stat.icon && typeof stat.icon === 'object' && (
              <img
                src={mediaSrc(stat.icon, 'small')}
                alt={stat.icon.alt || stat.label}
                className="w-12 h-12 mb-4 object-contain"
              />
            )}
            <span className="text-3xl font-bold text-gray-900">
              <CountUp value={stat.value} />
            </span>
            <span className="text-sm font-bold tracking-wider text-gray-600 uppercase mt-1">
              {stat.label}
            </span>
          </div>
        ))}
      </div>
    </div>
  )
}
