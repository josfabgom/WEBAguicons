'use client'
import { useEffect, useRef, useState } from 'react'
import type { PointerEvent as ReactPointerEvent } from 'react'

/**
 * Carrusel horizontal: avanza solo mientras el mouse está encima (vuelve al inicio al llegar al final)
 * y se puede arrastrar con el mouse (click sin soltar) hacia el lado que se quiera.
 */
export const useHoverScroll = (speed = 60) => {
  const ref = useRef<HTMLDivElement>(null)
  const [hovering, setHovering] = useState(false)
  const [dragging, setDragging] = useState(false)
  const drag = useRef({ moved: false })

  useEffect(() => {
    if (!hovering || dragging) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return
    let raf = 0
    let last = performance.now()
    const el0 = ref.current
    if (el0) {
      el0.style.scrollBehavior = 'auto'
      el0.style.scrollSnapType = 'none'
      if (el0.scrollLeft <= 1) el0.scrollLeft = el0.scrollWidth - el0.clientWidth
    }
    let pos = el0?.scrollLeft ?? 0
    const tick = (now: number) => {
      const el = ref.current
      if (el) {
        if (Math.abs(el.scrollLeft - pos) > 2) pos = el.scrollLeft
        pos -= ((now - last) / 1000) * speed
        if (pos <= 0) pos = el.scrollWidth - el.clientWidth
        el.scrollLeft = pos
      }
      last = now
      raf = requestAnimationFrame(tick)
    }
    raf = requestAnimationFrame(tick)
    return () => {
      cancelAnimationFrame(raf)
      if (el0) {
        el0.style.scrollBehavior = ''
        el0.style.scrollSnapType = ''
      }
    }
  }, [hovering, dragging, speed])

  const onPointerDown = (e: ReactPointerEvent<HTMLElement>) => {
    if (e.pointerType !== 'mouse' || e.button !== 0 || !ref.current) return
    const el = ref.current
    const startX = e.clientX
    const startLeft = el.scrollLeft
    drag.current.moved = false
    setDragging(true)
    const move = (ev: PointerEvent) => {
      const dx = ev.clientX - startX
      if (Math.abs(dx) > 4) drag.current.moved = true
      el.scrollLeft = startLeft - dx
    }
    const up = () => {
      window.removeEventListener('pointermove', move)
      window.removeEventListener('pointerup', up)
      window.removeEventListener('pointercancel', up)
      setDragging(false)
    }
    window.addEventListener('pointermove', move)
    window.addEventListener('pointerup', up)
    window.addEventListener('pointercancel', up)
  }

  return {
    ref,
    /** true mientras el mouse está encima o se está arrastrando (desactiva el snap) */
    hovering: hovering || dragging,
    dragging,
    handlers: {
      onMouseEnter: () => setHovering(true),
      onMouseLeave: () => setHovering(false),
      onPointerDown,
      // evita que un arrastre dispare el link de la tarjeta o el arrastre nativo de la imagen
      onClickCapture: (e: React.MouseEvent) => {
        if (drag.current.moved) {
          e.preventDefault()
          e.stopPropagation()
          drag.current.moved = false
        }
      },
      onDragStart: (e: React.DragEvent) => e.preventDefault(),
    },
  }
}
