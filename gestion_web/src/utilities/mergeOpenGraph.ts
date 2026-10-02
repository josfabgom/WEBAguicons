import type { Metadata } from 'next'
import { getServerSideURL } from './getURL'

const defaultOpenGraph: Metadata['openGraph'] = {
  type: 'website',
  description:
    'Aguicons: desarrolladora de Posadas, Misiones. Líneas edilicias de inversión y para vivir: Baradise, Velerian, Benrow, Tier y Alarif.',
  images: [
    {
      url: `${getServerSideURL()}/website-template-OG.webp`,
    },
  ],
  siteName: 'Aguicons',
  title: 'Aguicons - Construimos tu futuro',
  locale: 'es_AR',
}

export const mergeOpenGraph = (og?: Metadata['openGraph']): Metadata['openGraph'] => {
  return {
    ...defaultOpenGraph,
    ...og,
    images: og?.images ? og.images : defaultOpenGraph.images,
  }
}
