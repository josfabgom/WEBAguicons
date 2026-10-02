import type { Block } from 'payload'

export const StatsBlock: Block = {
  slug: 'stats',
  labels: {
    singular: 'Bloque de Estadísticas',
    plural: 'Bloques de Estadísticas',
  },
  fields: [
    {
      name: 'stats',
      type: 'array',
      label: 'Estadísticas',
      minRows: 1,
      maxRows: 4,
      fields: [
        {
          name: 'value',
          type: 'text',
          label: 'Valor (ej: +5.000)',
          required: true,
        },
        {
          name: 'label',
          type: 'text',
          label: 'Etiqueta (ej: Propietarios)',
          required: true,
        },
        {
          name: 'icon',
          type: 'upload',
          relationTo: 'media',
          label: 'Ícono',
        },
      ],
    },
  ],
}
