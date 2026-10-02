import type { Block } from 'payload'

export const TeamBlock: Block = {
  slug: 'team',
  labels: { singular: 'Equipo', plural: 'Equipos' },
  fields: [
    { name: 'title', type: 'text', label: 'Título', defaultValue: 'EQUIPO' },
    {
      name: 'members',
      type: 'array',
      label: 'Áreas / Integrantes',
      minRows: 1,
      fields: [
        { name: 'name', type: 'text', label: 'Nombre o área (ej: COMERCIAL)', required: true },
        { name: 'photo', type: 'upload', relationTo: 'media', label: 'Imagen' },
        { name: 'description', type: 'textarea', label: 'Descripción' },
      ],
    },
  ],
}
