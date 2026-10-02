import type { GlobalConfig } from 'payload'

import { link } from '@/fields/link'
import { revalidateFooter } from './hooks/revalidateFooter'

export const Footer: GlobalConfig = {
  slug: 'footer',
  label: 'Pie de Página',
  access: {
    read: () => true,
  },
  fields: [
    { name: 'whatsapp', type: 'text', label: 'WhatsApp (solo números, ej: 5493764630673)' },
    { name: 'copyright', type: 'text', label: 'Copyright', defaultValue: 'Copyright © 2026 Aguicons' },
    {
      name: 'navItems',
      type: 'array',
      fields: [
        link({
          appearances: false,
        }),
      ],
      maxRows: 6,
      admin: {
        initCollapsed: true,
        components: {
          RowLabel: '@/Footer/RowLabel#RowLabel',
        },
      },
    },
  ],
  hooks: {
    afterChange: [revalidateFooter],
  },
  versions: false,
}
