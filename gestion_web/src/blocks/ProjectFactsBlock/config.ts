import type { Block } from 'payload'

export const ProjectFactsBlock: Block = {
  slug: 'projectFacts',
  labels: { singular: 'Ficha técnica + mapa', plural: 'Fichas técnicas + mapa' },
  fields: [
    { name: 'title', type: 'text', label: 'Título', defaultValue: 'FICHA TÉCNICA' },
    {
      name: 'status',
      type: 'select',
      label: 'Estado del proyecto',
      options: [
        { label: 'En pozo', value: 'En pozo' },
        { label: 'En construcción', value: 'En construcción' },
        { label: 'Entregado', value: 'Entregado' },
        { label: 'Últimas unidades', value: 'Últimas unidades' },
      ],
    },
    {
      name: 'facts',
      type: 'array',
      label: 'Datos (ej: Superficie, Unidades, Entrega)',
      fields: [
        { name: 'label', type: 'text', label: 'Dato', required: true },
        { name: 'value', type: 'text', label: 'Valor', required: true },
      ],
    },
    {
      name: 'address',
      type: 'text',
      label: 'Dirección o lugar para el mapa (ej: Costanera, Posadas, Misiones)',
    },
  ],
}
