import React, { Fragment } from 'react'

import type { Page } from '@/payload-types'

import { ArchiveBlock } from '@/blocks/ArchiveBlock/Component'
import { CallToActionBlock } from '@/blocks/CallToAction/Component'
import { ContentBlock } from '@/blocks/Content/Component'
import { FormBlock } from '@/blocks/Form/Component'
import { MediaBlock } from '@/blocks/MediaBlock/Component'
import { StatsBlock } from '@/blocks/StatsBlock/Component'
import { CarouselBlock } from '@/blocks/CarouselBlock/Component'
import { LineCardsBlock } from '@/blocks/LineCardsBlock/Component'
import { TeamBlock } from '@/blocks/TeamBlock/Component'
import { ProjectIntroBlock } from '@/blocks/ProjectIntroBlock/Component'
import { ContactBarBlock } from '@/blocks/ContactBarBlock/Component'
import { BannerBlock } from '@/blocks/BannerBlock/Component'
import { FeaturedCardsBlock } from '@/blocks/FeaturedCardsBlock/Component'
import { LineLinksBlock } from '@/blocks/LineLinksBlock/Component'
import { Reveal } from '@/components/Reveal'
import { InquiryBlock, BrochureBlock } from '@/blocks/LeadFormBlock/Component'
import { ProjectFactsBlock } from '@/blocks/ProjectFactsBlock/Component'
import { TestimonialsBlock } from '@/blocks/TestimonialsBlock/Component'

const blockComponents = {
  archive: ArchiveBlock,
  content: ContentBlock,
  cta: CallToActionBlock,
  formBlock: FormBlock,
  mediaBlock: MediaBlock,
  stats: StatsBlock,
  carousel: CarouselBlock,
  lineCards: LineCardsBlock,
  team: TeamBlock,
  projectIntro: ProjectIntroBlock,
  contactBar: ContactBarBlock,
  banner: BannerBlock,
  featuredCards: FeaturedCardsBlock,
  lineLinks: LineLinksBlock,
  inquiry: InquiryBlock,
  brochure: BrochureBlock,
  projectFacts: ProjectFactsBlock,
  testimonials: TestimonialsBlock,
}

export const RenderBlocks: React.FC<{
  blocks: Page['layout'][0][]
}> = (props) => {
  const { blocks } = props

  const hasBlocks = blocks && Array.isArray(blocks) && blocks.length > 0

  if (hasBlocks) {
    return (
      <Fragment>
        {blocks.map((block, index) => {
          const { blockType } = block

          if (blockType && blockType in blockComponents) {
            const Block = blockComponents[blockType]

            if (Block) {
              const flush = blockType === 'banner' || blockType === 'projectIntro'
              const rendered = (
                // @ts-expect-error there may be some mismatch between the expected types here
                <Block {...block} disableInnerContainer />
              )
              return flush ? (
                <div className="mb-16" key={index}>
                  {rendered}
                </div>
              ) : (
                <Reveal className="my-16" key={index}>
                  {rendered}
                </Reveal>
              )
            }
          }
          return null
        })}
      </Fragment>
    )
  }

  return null
}
