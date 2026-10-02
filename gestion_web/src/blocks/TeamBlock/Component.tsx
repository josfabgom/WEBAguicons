import React from 'react'

import { mediaSrc } from '@/utilities/mediaSrc'

export const TeamBlock: React.FC<any> = ({ title, members }) => {
  return (
    <div className="container py-12">
      {title && (
        <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
      )}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {members?.map((m: any, i: number) => (
          <div key={i} className="flex flex-col">
            <div className="bg-white border border-border aspect-[3/4] flex items-center justify-center overflow-hidden">
              {m.photo && typeof m.photo === 'object' ? (
                <img
                  src={mediaSrc(m.photo, 'medium')}
                  loading="lazy"
                  alt={m.photo.alt || m.name}
                  className="w-full h-full object-cover"
                />
              ) : (
                <span className="font-bold uppercase tracking-wider">{m.name}</span>
              )}
            </div>
            {m.photo && (
              <span className="mt-2 text-center font-bold uppercase tracking-wider">{m.name}</span>
            )}
            {m.description && (
              <p className="mt-1 text-center text-xs text-muted-foreground">{m.description}</p>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
