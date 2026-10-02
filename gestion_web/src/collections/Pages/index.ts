import type { CollectionConfig } from 'payload'
import { slugField } from '@/fields/slug'

import { authenticated } from '../../access/authenticated'
import { authenticatedOrPublished } from '../../access/authenticatedOrPublished'
import { Archive } from '../../blocks/ArchiveBlock/config'
import { CallToAction } from '../../blocks/CallToAction/config'
import { Content } from '../../blocks/Content/config'
import { FormBlock } from '../../blocks/Form/config'
import { MediaBlock } from '../../blocks/MediaBlock/config'
import { StatsBlock } from '../../blocks/StatsBlock/config'
import { CarouselBlock } from '../../blocks/CarouselBlock/config'
import { LineCardsBlock } from '../../blocks/LineCardsBlock/config'
import { TeamBlock } from '../../blocks/TeamBlock/config'
import { ProjectIntroBlock } from '../../blocks/ProjectIntroBlock/config'
import { ContactBarBlock } from '../../blocks/ContactBarBlock/config'
import { LineLinksBlock } from '../../blocks/LineLinksBlock/config'
import { InquiryBlock, BrochureBlock } from '../../blocks/LeadFormBlock/config'
import { ProjectFactsBlock } from '../../blocks/ProjectFactsBlock/config'
import { TestimonialsBlock } from '../../blocks/TestimonialsBlock/config'
import { BannerBlock } from '../../blocks/BannerBlock/config'
import { FeaturedCardsBlock } from '../../blocks/FeaturedCardsBlock/config'
import { hero } from '@/heros/config'
import { populatePublishedAt } from '../../hooks/populatePublishedAt'
import { generatePreviewPath } from '../../utilities/generatePreviewPath'
import { revalidateDelete, revalidatePage } from './hooks/revalidatePage'

import {
  MetaDescriptionField,
  MetaImageField,
  MetaTitleField,
  OverviewField,
  PreviewField,
} from '@payloadcms/plugin-seo/fields'

export const Pages: CollectionConfig = {
  slug: 'pages',
  labels: { singular: 'Página', plural: 'Páginas' },
  access: {
    create: authenticated,
    delete: authenticated,
    read: authenticatedOrPublished,
    update: authenticated,
  },
  // This config controls what's populated by default when a page is referenced
  // https://payloadcms.com/docs/queries/select#defaultpopulate-collection-config-property
  // NOTE: the slug generic (CollectionConfig<'pages'>) is dropped to work around a TypeScript 6
  // regression where a slug-typed config's defaultPopulate is not assignable to buildConfig's
  // collections array. defaultPopulate falls back to SelectType (keys no longer field-checked);
  // restore the generic once the core types are fixed.
  defaultPopulate: {
    title: true,
    slug: true,
  },
  admin: {
    defaultColumns: ['title', 'slug', 'updatedAt'],
    livePreview: {
      url: ({ data, req }) =>
        generatePreviewPath({
          slug: data?.slug,
          collection: 'pages',
          req,
        }),
    },
    preview: (data, { req }) =>
      generatePreviewPath({
        slug: data?.slug as string,
        collection: 'pages',
        req,
      }),
    useAsTitle: 'title',
  },
  fields: [
    {
      name: 'title',
      type: 'text',
      required: true,
    },
    {
      type: 'tabs',
      tabs: [
        {
          fields: [hero],
          label: 'Hero',
        },
        {
          fields: [
            {
              name: 'layout',
              type: 'blocks',
              blocks: [CallToAction, Content, MediaBlock, Archive, FormBlock, StatsBlock, CarouselBlock, LineCardsBlock, TeamBlock, ProjectIntroBlock, ContactBarBlock, BannerBlock, FeaturedCardsBlock, LineLinksBlock, InquiryBlock, BrochureBlock, ProjectFactsBlock, TestimonialsBlock],
              required: true,
              admin: {
                initCollapsed: true,
              },
            },
          ],
          label: 'Content',
        },
        {
          name: 'meta',
          label: 'SEO',
          fields: [
            OverviewField({
              titlePath: 'meta.title',
              descriptionPath: 'meta.description',
              imagePath: 'meta.image',
            }),
            MetaTitleField({
              hasGenerateFn: true,
            }),
            MetaImageField({
              relationTo: 'media',
            }),

            MetaDescriptionField({}),
            PreviewField({
              // if the `generateUrl` function is configured
              hasGenerateFn: true,

              // field paths to match the target field for data
              titlePath: 'meta.title',
              descriptionPath: 'meta.description',
            }),
          ],
        },
      ],
    },
    {
      name: 'background',
      type: 'select',
      label: 'Fondo de la página',
      defaultValue: 'default',
      options: [
        { label: 'Blanco', value: 'default' },
        { label: 'Dorado', value: 'gold' },
      ],
      admin: { position: 'sidebar' },
    },
    {
      name: 'publishedAt',
      type: 'date',
      admin: {
        position: 'sidebar',
      },
    },
    slugField('title'),
  ],
  hooks: {
    afterChange: [revalidatePage],
    beforeChange: [populatePublishedAt],
    afterDelete: [revalidateDelete],
  },
  versions: {
    drafts: {
      autosave: {
        interval: 100, // We set this interval for optimal live preview
      },
      schedulePublish: true,
    },
    maxPerDoc: 50,
  },
}
