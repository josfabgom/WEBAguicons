import type { Block } from 'payload'

export const ContactBarBlock: Block = {
  slug: 'contactBar',
  labels: { singular: 'Barra de Contacto', plural: 'Barras de Contacto' },
  fields: [
    {
      name: 'address',
      type: 'text',
      label: 'Dirección',
      defaultValue: 'Av. Antartida Argentina 876',
    },
    {
      name: 'buttonLabel',
      type: 'text',
      label: 'Texto del botón',
      defaultValue: 'CONVERSÁ CON NOSOTROS',
    },
    { name: 'buttonUrl', type: 'text', label: 'URL del botón (ej: /contacto o link de WhatsApp)' },
    {
      name: 'contactLine',
      type: 'text',
      label: 'Email y teléfono',
      defaultValue: 'info@aguicons.com - +54 9 3764 630673',
    },
    { name: 'showBack', type: 'checkbox', label: 'Mostrar botón VOLVER', defaultValue: false },
    { name: 'backUrl', type: 'text', label: 'URL de VOLVER', defaultValue: '/' },
  ],
}
