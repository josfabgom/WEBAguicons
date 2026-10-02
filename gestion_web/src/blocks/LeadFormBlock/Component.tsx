'use client'
import React, { useState } from 'react'

const input =
  'w-full border border-border rounded-lg px-3 py-2 bg-white text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-gold'

const track = (event: string, params: Record<string, any>) => {
  try {
    ;(window as any).gtag?.('event', event, params)
  } catch {}
}

type Mode = 'inquiry' | 'brochure'

const LeadForm: React.FC<any> = ({ mode, title, project, form, file }) => {
  const [state, setState] = useState<'idle' | 'sending' | 'done' | 'error'>('idle')
  const formId = typeof form === 'object' ? form?.id : form
  const isBrochure = mode === 'brochure'

  const onSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    const data = Object.fromEntries(new FormData(e.currentTarget).entries())
    if (data.website) return // trampa anti-spam
    setState('sending')
    try {
      const submissionData = [
        { field: 'proyecto', value: project },
        { field: 'tipo', value: isBrochure ? 'Descarga de brochure' : 'Consulta' },
        ...['nombre', 'email', 'telefono', 'mensaje']
          .filter((k) => data[k])
          .map((k) => ({ field: k, value: String(data[k]) })),
      ]
      const res = await fetch('/api/form-submissions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ form: formId, submissionData }),
      })
      if (!res.ok) throw new Error(String(res.status))
      track('generate_lead', { project, lead_type: isBrochure ? 'brochure' : 'consulta' })
      setState('done')
    } catch {
      setState('error')
    }
  }

  const heading = title || (isBrochure ? `Descargá el brochure de ${project}` : `Quiero información de ${project}`)

  return (
    <section className="container max-w-xl">
      <h2 className="text-center font-bold text-xl tracking-widest uppercase mb-6">{heading}</h2>
      {state === 'done' ? (
        <div role="status" className="border border-gold rounded-xl p-6 text-center">
          <p className="font-bold mb-2">¡Gracias! Recibimos tus datos.</p>
          {isBrochure && file && typeof file === 'object' ? (
            <a
              href={file.url}
              download
              onClick={() => track('file_download', { project })}
              className="inline-block mt-2 bg-gold text-white font-bold uppercase tracking-widest rounded-xl px-6 py-2"
            >
              Descargar brochure
            </a>
          ) : (
            <p className="text-sm">
              {isBrochure
                ? 'Te enviaremos el brochure por email a la brevedad.'
                : 'Un asesor se comunicará con vos a la brevedad.'}
            </p>
          )}
        </div>
      ) : (
        <form onSubmit={onSubmit} className="flex flex-col gap-3 border border-border rounded-xl p-5">
          <label className="text-sm font-bold">
            Nombre y apellido*
            <input name="nombre" required autoComplete="name" className={`${input} mt-1 font-normal`} />
          </label>
          <label className="text-sm font-bold">
            Email*
            <input
              name="email"
              type="email"
              required
              autoComplete="email"
              className={`${input} mt-1 font-normal`}
            />
          </label>
          {!isBrochure && (
            <>
              <label className="text-sm font-bold">
                Teléfono
                <input name="telefono" type="tel" autoComplete="tel" className={`${input} mt-1 font-normal`} />
              </label>
              <label className="text-sm font-bold">
                Mensaje
                <textarea name="mensaje" rows={3} className={`${input} mt-1 font-normal`} />
              </label>
            </>
          )}
          {/* campo trampa para bots: oculto a personas */}
          <input name="website" tabIndex={-1} autoComplete="off" aria-hidden="true" className="hidden" />
          <p className="text-xs text-muted-foreground">
            Al enviar aceptás nuestra{' '}
            <a href="/politica-de-privacidad" className="underline">
              política de privacidad
            </a>
            .
          </p>
          {state === 'error' && (
            <p role="alert" className="text-sm text-red-600">
              No pudimos enviar el formulario. Probá de nuevo o escribinos por WhatsApp.
            </p>
          )}
          <button
            type="submit"
            disabled={state === 'sending'}
            className="bg-gold text-white font-bold uppercase tracking-widest rounded-xl px-6 py-3 disabled:opacity-60 hover:opacity-90"
          >
            {state === 'sending' ? 'Enviando…' : isBrochure ? 'Recibir brochure' : 'Enviar consulta'}
          </button>
        </form>
      )}
    </section>
  )
}

export const InquiryBlock: React.FC<any> = (props) => <LeadForm mode={'inquiry' as Mode} {...props} />
export const BrochureBlock: React.FC<any> = (props) => <LeadForm mode={'brochure' as Mode} {...props} />
