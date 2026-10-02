import type { Block } from 'payload'

export const LineLinksBlock: Block = {
  slug: 'lineLinks',
  labels: { singular: 'Otras líneas (accesos)', plural: 'Otras líneas (accesos)' },
  fields: [
    { name: 'title', type: 'text', label: 'Título', defaultValue: 'OTRAS LÍNEAS' },
    {
      name: 'links',
      type: 'array',
      label: 'Accesos',
      minRows: 1,
      fields: [
        { name: 'label', type: 'text', label: 'Texto', required: true },
        { name: 'url', type: 'text', label: 'URL (ej: /tier)', required: true },
      ],
    },
  ],
}
