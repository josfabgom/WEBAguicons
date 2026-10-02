import Link from 'next/link'
import React from 'react'

export const LineLinksBlock: React.FC<any> = ({ title, links }) => {
  return (
    <nav aria-label={title || 'Otras líneas'} className="container text-center py-6">
      {title && <h2 className="font-bold text-lg tracking-widest uppercase mb-4">{title}</h2>}
      <ul className="flex flex-wrap justify-center gap-3">
        {links?.map((l: any, i: number) => (
          <li key={i}>
            <Link
              href={l.url}
              className="inline-block border-2 border-gold rounded-xl px-6 py-2 text-sm font-bold uppercase tracking-widest hover:bg-gold hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold"
            >
              {l.label}
            </Link>
          </li>
        ))}
      </ul>
    </nav>
  )
}
