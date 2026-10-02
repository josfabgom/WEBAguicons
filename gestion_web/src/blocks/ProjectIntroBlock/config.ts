import type { Block } from 'payload'

export const ProjectIntroBlock: Block = {
  slug: 'projectIntro',
  labels: { singular: 'Ficha de Proyecto (fondo negro)', plural: 'Fichas de Proyecto' },
  fields: [
    { name: 'logo', type: 'upload', relationTo: 'media', label: 'Logo del proyecto / línea' },
    { name: 'name', type: 'text', label: 'Nombre', required: true },
    { name: 'scriptTitle', type: 'checkbox', label: 'Título en tipografía decorativa (estilo Baradise)' },
    { name: 'text', type: 'textarea', label: 'Descripción', required: true },
    {
      name: 'amenities',
      type: 'array',
      label: 'Amenities (ej: Piscina, Solárium)',
      fields: [{ name: 'label', type: 'text', required: true }],
    },
  ],
}
