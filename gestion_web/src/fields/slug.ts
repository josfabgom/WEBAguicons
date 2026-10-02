import type { Field } from 'payload'

const formatSlug = (v: string): string =>
  v
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

export const slugField = (fieldToUse = 'title'): Field => ({
  name: 'slug',
  type: 'text',
  index: true,
  admin: { position: 'sidebar' },
  hooks: {
    beforeValidate: [
      ({ value, data }) => {
        if (typeof value === 'string' && value.length > 0) return formatSlug(value)
        const source = data?.[fieldToUse]
        return typeof source === 'string' ? formatSlug(source) : value
      },
    ],
  },
})
