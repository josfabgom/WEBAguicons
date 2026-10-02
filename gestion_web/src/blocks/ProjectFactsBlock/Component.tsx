import React from 'react'

export const ProjectFactsBlock: React.FC<any> = ({ title, status, facts, address }) => {
  const hasFacts = facts?.length > 0
  if (!hasFacts && !status && !address) return null
  const q = address ? encodeURIComponent(address) : ''

  return (
    <section className="container max-w-5xl">
      {title && (
        <h2 className="text-center font-bold text-2xl tracking-widest uppercase mb-8">{title}</h2>
      )}
      <div className="grid gap-8 md:grid-cols-2 items-start">
        <div>
          {status && (
            <p className="mb-4">
              <span className="inline-block bg-gold text-white text-xs font-bold uppercase tracking-widest px-3 py-1 rounded">
                {status}
              </span>
            </p>
          )}
          {hasFacts && (
            <dl className="divide-y divide-border border border-border rounded-xl">
              {facts.map((f: any, i: number) => (
                <div key={i} className="flex justify-between gap-4 px-4 py-3 text-sm">
                  <dt className="font-bold">{f.label}</dt>
                  <dd className="text-right text-muted-foreground">{f.value}</dd>
                </div>
              ))}
            </dl>
          )}
        </div>
        {address && (
          <div>
            <iframe
              title={`Mapa: ${address}`}
              src={`https://www.google.com/maps?q=${q}&output=embed`}
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              className="w-full h-72 rounded-xl border border-border"
            />
            <a
              href={`https://www.google.com/maps/search/?api=1&query=${q}`}
              target="_blank"
              rel="noopener noreferrer"
              className="inline-block mt-2 text-sm underline"
            >
              Ver en Google Maps
            </a>
          </div>
        )}
      </div>
    </section>
  )
}
