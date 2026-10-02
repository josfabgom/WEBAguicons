import type { Block, Field } from 'payload'

const common: Field[] = [
  { name: 'title', type: 'text', label: 'Título' },
  {
    name: 'project',
    type: 'text',
    label: 'Proyecto / línea (se envía con la consulta, ej: Baradise)',
    required: true,
  },
  {
    name: 'form',
    type: 'relationship',
    relationTo: 'forms',
    label: 'Formulario (define a quién y cómo se avisa por email)',
    required: true,
  },
]

export const InquiryBlock: Block = {
  slug: 'inquiry',
  labels: { singular: 'Consulta por proyecto', plural: 'Consultas por proyecto' },
  fields: [...common],
}

export const BrochureBlock: Block = {
  slug: 'brochure',
  labels: { singular: 'Descarga de brochure', plural: 'Descargas de brochure' },
  fields: [
    ...common,
    {
      name: 'file',
      type: 'upload',
      relationTo: 'media',
      label: 'Archivo PDF del brochure (si no se carga, se avisa que se enviará por email)',
    },
  ],
}
