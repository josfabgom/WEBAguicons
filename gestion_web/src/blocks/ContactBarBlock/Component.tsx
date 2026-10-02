import Link from 'next/link'
import React from 'react'

export const ContactBarBlock: React.FC<any> = ({
  address,
  buttonLabel,
  buttonUrl,
  contactLine,
  showBack,
  backUrl,
}) => {
  return (
    <div className="container py-10 flex flex-col items-center gap-4 text-center">
      {address && <p className="text-sm">📍 {address}</p>}
      <Link
        href={buttonUrl || '/contacto'}
        className="bg-gold text-white font-bold tracking-widest uppercase rounded-xl px-8 py-3 hover:opacity-90"
      >
        {buttonLabel}
      </Link>
      {contactLine && <p className="text-xs">{contactLine}</p>}
      {showBack && (
        <Link
          href={backUrl || '/'}
          className="border-2 border-gold rounded-xl px-10 py-2 font-bold tracking-widest uppercase hover:bg-gold hover:text-white"
        >
          Volver
        </Link>
      )}
    </div>
  )
}
