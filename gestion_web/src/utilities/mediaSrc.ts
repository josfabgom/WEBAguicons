type Size = 'thumbnail' | 'small' | 'medium' | 'large' | 'xlarge'

const ORDER: Size[] = ['xlarge', 'large', 'medium', 'small', 'thumbnail']

/**
 * URL de la variante optimizada (WebP) más cercana al tamaño pedido.
 * Payload no agranda imágenes, así que si falta el tamaño pedido se usa la variante menor disponible
 * y, solo si no hay ninguna, el original.
 */
export const mediaSrc = (media: any, size: Size = 'large'): string => {
  if (!media || typeof media !== 'object') return ''
  for (const s of ORDER.slice(ORDER.indexOf(size))) {
    const url = media.sizes?.[s]?.url
    if (url) return url
  }
  return media.url || ''
}
