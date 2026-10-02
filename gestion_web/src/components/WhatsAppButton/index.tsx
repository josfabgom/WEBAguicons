'use client'
import { usePathname } from 'next/navigation'
import React from 'react'

const NAMES: Record<string, string> = {
  baradise: 'Baradise',
  velerian: 'Velerian',
  benrow: 'Benrow',
  'benrow-acros': 'Benrow Acros',
  tier: 'Tier',
  vantier: 'Vantier',
  loftier: 'Loftier',
  alarif: 'Alarif',
  'alarif-fazara': 'Alarif Fazara',
  'alarif-trench': 'Alarif Trench',
  alquiler: 'alquileres',
  'servicios-de-construccion': 'servicios de construcción',
  'movimientos-de-suelo': 'movimiento de suelo',
}

/** Botón flotante de WhatsApp: el mensaje inicial menciona el proyecto de la página que se está viendo. */
export const WhatsAppButton: React.FC<{ number: string }> = ({ number }) => {
  const slug = (usePathname() || '/').split('/')[1] || ''
  const topic = NAMES[slug]
  const text = topic
    ? `Hola, quiero más información sobre ${topic}.`
    : 'Hola, quiero más información sobre Aguicons.'

  return (
    <a
      href={`https://wa.me/${number}?text=${encodeURIComponent(text)}`}
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Escribinos por WhatsApp"
      onClick={() => {
        try {
          ;(window as any).gtag?.('event', 'whatsapp_click', { page: slug || 'home', topic })
        } catch {}
      }}
      className="fixed bottom-6 right-6 z-50 flex size-14 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition-transform hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
    >
      <svg viewBox="0 0 24 24" className="size-8" fill="currentColor" aria-hidden="true">
        <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.2 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .5l-.4.6c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.8-.1 1.4Z" />
      </svg>
    </a>
  )
}
