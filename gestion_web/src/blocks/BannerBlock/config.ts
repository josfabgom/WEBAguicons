import type { Block } from 'payload'

import { link } from '@/fields/link'

export const BannerBlock: Block = {
  slug: 'banner',
  labels: { singular: 'Banner de portada', plural: 'Banners de portada' },
  fields: [
    { name: 'image', type: 'upload', relationTo: 'media', label: 'Imagen', required: true },
    { name: 'title', type: 'text', label: 'Título (antes del acento)' },
    { name: 'titleAccent', type: 'text', label: 'Palabra en cursiva' },
    { name: 'titleAfter', type: 'text', label: 'Título (después del acento)' },
    link({ appearances: false }),
  ],
}
