import type { Block } from 'payload'

import { link } from '@/fields/link'

export const LineCardsBlock: Block = {
  slug: 'lineCards',
  labels: { singular: 'Líneas Edilicias', plural: 'Líneas Edilicias' },
  fields: [
    { name: 'title', type: 'text', label: 'Título', defaultValue: 'NUESTRAS LINEAS EDILICIAS' },
    {
      name: 'cards',
      type: 'array',
      label: 'Tarjetas',
      minRows: 1,
      fields: [
        { name: 'name', type: 'text', label: 'Nombre', required: true },
        { name: 'logo', type: 'upload', relationTo: 'media', label: 'Logo (sobre fondo negro)' },
        link({ appearances: false, disableLabel: true }),
      ],
    },
  ],
}
