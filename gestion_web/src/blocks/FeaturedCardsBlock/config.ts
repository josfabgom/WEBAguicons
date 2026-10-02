import type { Block } from 'payload'

import { link } from '@/fields/link'

export const FeaturedCardsBlock: Block = {
  slug: 'featuredCards',
  labels: { singular: 'Tarjetas protagónicas', plural: 'Tarjetas protagónicas' },
  fields: [
    { name: 'title', type: 'text', label: 'Título (ej: PROTAGÓNICO (ACROS))' },
    {
      name: 'cards',
      type: 'array',
      label: 'Tarjetas',
      minRows: 1,
      fields: [
        { name: 'name', type: 'text', label: 'Nombre', required: true },
        { name: 'image', type: 'upload', relationTo: 'media', label: 'Imagen' },
        { name: 'badgeTitle', type: 'text', label: 'Cartel (ej: ALQUILER)' },
        { name: 'badgeText', type: 'text', label: 'Cartel - detalle (ej: Local Comercial)' },
        link({ appearances: false, disableLabel: true }),
      ],
    },
  ],
}
