import type { Block } from 'payload'

export const CarouselBlock: Block = {
  slug: 'carousel',
  labels: {
    singular: 'Carrusel de Vistas',
    plural: 'Carruseles de Vistas',
  },
  fields: [
    {
      name: 'title',
      type: 'text',
      label: 'Título (ej: CARRUSEL DE VISTAS (OBRAS))',
    },
    {
      name: 'images',
      type: 'array',
      label: 'Imágenes',
      minRows: 1,
      fields: [
        {
          name: 'image',
          type: 'upload',
          relationTo: 'media',
          required: true,
        },
      ],
    },
  ],
}
