import { getCachedGlobal } from '@/utilities/getGlobals'
import Link from 'next/link'
import React from 'react'

import { CMSLink } from '@/components/Link'
import { GoldLogo } from '@/components/GoldLogo'
import { WhatsAppButton } from '@/components/WhatsAppButton'

export async function Footer() {
  const footerData = await getCachedGlobal('footer', 1)()

  const navItems = footerData?.navItems || []

  return (
    <footer className="mt-auto border-t border-border bg-black text-white">
      <div className="container py-8 flex flex-col items-center gap-5 text-center">
        <Link href="/" aria-label="Aguicons - Inicio">
          <GoldLogo className="w-24" />
        </Link>
        {navItems.length > 0 && (
          <nav aria-label="Pie de página" className="flex flex-wrap justify-center gap-4">
            {navItems.map(({ link }, i) => {
              return <CMSLink className="text-white" key={i} {...link} />
            })}
          </nav>
        )}
        <p className="text-xs text-white/70">
          {footerData?.copyright}
          <span className="mx-2">·</span>
          Powered by BuenaIdea &amp; PriZa
        </p>
      </div>
      {footerData?.whatsapp && <WhatsAppButton number={footerData.whatsapp} />}
      </footer>
  )
}
