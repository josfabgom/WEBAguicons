import type { Block } from 'payload'

export const TestimonialsBlock: Block = {
  slug: 'testimonials',
  labels: { singular: 'Testimonios', plural: 'Testimonios' },
  fields: [
    { name: 'title', type: 'text', label: 'Título', defaultValue: 'LO QUE DICEN NUESTROS CLIENTES' },
    {
      name: 'items',
      type: 'array',
      label: 'Testimonios (cargar solo opiniones reales y autorizadas)',
      minRows: 1,
      fields: [
        { name: 'quote', type: 'textarea', label: 'Testimonio', required: true },
        { name: 'name', type: 'text', label: 'Nombre', required: true },
        { name: 'role', type: 'text', label: 'Rol (ej: Propietario Baradise, Inversor)' },
        { name: 'photo', type: 'upload', relationTo: 'media', label: 'Foto (opcional)' },
      ],
    },
  ],
}
