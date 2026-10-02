import config from '@payload-config'
import { getPayload } from 'payload'

const text = (t: string) => ({
  type: 'text',
  text: t,
  version: 1,
  format: 0,
  detail: 0,
  mode: 'normal',
  style: '',
})
const para = (t: string) => ({
  type: 'paragraph',
  version: 1,
  format: '',
  indent: 0,
  direction: 'ltr',
  textFormat: 0,
  children: [text(t)],
})
const heading = (t: string, tag = 'h2') => ({
  type: 'heading',
  tag,
  version: 1,
  format: '',
  indent: 0,
  direction: 'ltr',
  children: [text(t)],
})
const lexical = (...children: any[]) => ({
  root: { type: 'root', format: '', indent: 0, version: 1, direction: 'ltr', children },
})

const LOREM =
  'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.'

const IMG = 'C:/Cloude Proyectos/PaginasWEBQ/Documentos/IMAGENES'
const mediaIds: Record<string, number | string> = {}
let payloadRef: any
let formId: number | string
let inquiryFormId: number | string
let brochureFormId: number | string
const img = async (rel: string, alt = '') => {
  if (mediaIds[rel]) return mediaIds[rel]
  const filename = rel
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/[^A-Za-z0-9.-]+/g, '_')
  const found = await payloadRef.find({ collection: 'media', where: { filename: { equals: filename } }, limit: 1 })
  if (found.docs[0]) return (mediaIds[rel] = found.docs[0].id)
  const fs = await import('fs')
  const tmp = (await import('os')).tmpdir() + '/' + filename
  fs.copyFileSync(`${IMG}/${rel}`, tmp)
  const auto = rel
    .split('/')
    .slice(0, -1)
    .filter((s) => !/^(HOME|LINEAS EDILICIAS|\d+\. )/.test(s))
    .map((s) => s.charAt(0) + s.slice(1).toLowerCase())
    .join(' - ')
  const doc = await payloadRef.create({
    collection: 'media',
    data: { alt: alt || (auto ? `Aguicons - ${auto}` : 'Aguicons') },
    filePath: tmp,
  })
  return (mediaIds[rel] = doc.id)
}
const imgs = (rels: string[], alt = '') => Promise.all(rels.map((r) => img(r, alt)))
const slides = (ids: any[]) => ids.map((image) => ({ image }))
const L = 'LINEAS EDILICIAS'

const contact = (showBack: boolean) => ({
  blockType: 'contactBar',
  address: 'Av. Antartida Argentina 876',
  buttonLabel: 'CONVERSÁ CON NOSOTROS',
  contactLine: 'info@aguicons.com - +54 9 3764 630673',
  showBack,
  backUrl: '/',
})

const content = (...children: any[]) => ({
  blockType: 'content',
  columns: [{ size: 'full', richText: lexical(...children), enableLink: false }],
})

const nosotrosText =
  'En AGUICONS® cambiamos el skyline de las ciudades. Y comenzamos por la nuestra, en Posadas, Misiones, Argentina, hace más de 15 años. Ésto nos define como empresa y nos motiva a replicarlo en todo el mundo aportando valor a cada contexto de manera innovadora, auténtica y confiable. La manera en que lo hacemos es a través de nuestro diferencial exclusivo como desarrolladora: líneas edilicias enfocadas en un abanico de experiencias —tanto de inversión como para vivir—. Y es en función de esto que organizamos nuestros procesos internos: no solo con la vanguardia en tecnología y certificaciones (BIM) sino poniendo en el centro al factor humano. En cada área AGUICONS® se potencia con profesionales expertos que aportan un caudal de conocimientos y nuevas ideas para contribuir al crecimiento y mejora de la sociedad. Porque eso es lo que nos guía: ser partícipes en construir el mañana.'

const servicios = [
  'Ofrecemos soluciones integrales de construcción para el sector público y privado, ejecutando cada proyecto con solvencia técnica, eficiencia y cumplimiento riguroso de plazos. Nuestra trayectoria abarca obras de diversa escala y complejidad técnica.',
  'Principales Proyectos y Tipologías: Contamos con experiencia en arquitectura comercial y bancaria, incluyendo sucursales financieras como Banco Macro, oficinas corporativas y locales comerciales; desarrollo residencial, con viviendas unifamiliares de categoría y edificios multifamiliares de propiedad horizontal; infraestructura pública y de salud, como centros hospitalarios, establecimientos educativos y arquitectura institucional; y urbanización y espacios públicos, con la construcción de barrios de viviendas, infraestructura urbana y plazas comunitarias.',
  'Garantizamos una dirección de obra rigurosa, gestión eficiente de recursos y estándares superiores de calidad constructiva de principio a fin.',
]

const suelo = [
  'Toda gran obra comienza por su vínculo con el terreno. El movimiento de suelos es la etapa en la que preparamos y transformamos el suelo natural para adaptarlo a las exigencias de cada proyecto, definiendo los niveles y condiciones necesarios para recibir sus fundaciones y estructuras.',
  'Contamos con maquinaria y equipamiento especializado de distintas capacidades, seleccionados según las características y requerimientos de cada intervención. Este equipamiento nos permite abordar trabajos de excavación, apertura y preparación de calles, demoliciones, desmontes previos al movimiento de suelos y adecuación de terrenos para proyectos de diversa escala, desde viviendas hasta desarrollos edilicios de mayor envergadura.',
  'Una primera intervención que, aunque permanece bajo la superficie, resulta fundamental para dar solidez a todo aquello que vendrá después.',
]

const baradiseText =
  'Fiel a la tradición de Aguicons® de bautizar con un nombre propio y exclusivo al proyecto insignia de cada nueva línea. Baradise® nace para inaugurar nuestra propuesta residencial más elevada hasta el momento. La expresión máxima de lujo, arquitectura orgánica y diseño contemporáneo frente al río.\n\nBARADISE® propone una forma sutil y renovada de habitar la ciudad a través de una arquitectura que combina líneas contemporáneas y materiales nobles. Concebido para integrarse de manera fluida con su entorno, el edificio aprovecha grandes ventanales y distribuciones abiertas que invitan a la luz natural y abren vistas panorámicas hacia el río, creando espacios que transmiten libertad, calma y confort.\n\nCon exclusivas residencias de 2 y 3 dormitorios en suite proyectadas al detalle.'

const intro = (name: string, text = LOREM, amenities: string[] = [], scriptTitle = false) => ({
  blockType: 'projectIntro',
  scriptTitle,
  name,
  text,
  amenities: amenities.map((label) => ({ label })),
})
const carousel = (title: string, ids: any[]) => ({ blockType: 'carousel', title, images: slides(ids) })
const cards = (title: string, list: any[]) => ({ blockType: 'featuredCards', title, cards: list })
const go = (slug: string) => ({ type: 'custom', url: `/${slug}` })
const simple = (slug: string, title: string, layout: any[]) => ({
  slug,
  title,
  hero: { type: 'none' },
  layout,
})

const PRIVACY: [string, string][] = [
  ['Responsable', 'Aguicons, con domicilio en Av. Antártida Argentina 876, Posadas, Misiones, Argentina (info@aguicons.com), es responsable del tratamiento de los datos personales recabados en este sitio.'],
  ['Datos que recolectamos', 'Los que nos brindás al completar formularios (nombre, email, teléfono, mensaje y proyecto de interés) y, si aceptás las cookies, datos anónimos de navegación mediante Google Analytics.'],
  ['Finalidad', 'Responder tus consultas, enviarte la información o el brochure solicitado y contactarte por la propiedad de tu interés. No vendemos ni cedemos tus datos a terceros ajenos a estos fines.'],
  ['Cookies y analítica', 'Usamos cookies de analítica solo con tu consentimiento, para entender cómo se usa el sitio y mejorarlo. Podés rechazarlas desde el aviso que aparece al ingresar.'],
  ['Tus derechos', 'Conforme a la Ley 25.326 de Protección de Datos Personales, podés acceder, rectificar y suprimir tus datos escribiendo a info@aguicons.com. La Agencia de Acceso a la Información Pública, en su carácter de órgano de control, tiene la atribución de atender denuncias y reclamos por incumplimientos a las normas de protección de datos personales.'],
  ['Conservación', 'Conservamos tus datos mientras sea necesario para atender tu consulta y por los plazos que exija la ley.'],
  ['Cambios', 'Podemos actualizar esta política; la versión vigente es la publicada en esta página.'],
]

const buildPages = async (): Promise<any[]> => {
  const portada = await img(
    'HOME/BARADISE/Copia de 03.png',
    'Construimos tu futuro',
  )
  const homeCards = []
  for (const [name, slug, rel] of [
    ['Baradise', 'baradise', 'HOME/BARADISE/Copia de 03.png'],
    ['Velerian', 'velerian', 'HOME/VELERIAN/Copia de 06.png'],
    ['Benrow', 'benrow', 'HOME/BENROW/Copia de 19.png'],
    ['Tier', 'tier', 'HOME/TIER/Copia de 17.png'],
    ['Alarif', 'alarif', 'HOME/ALARIF/Copia de 1.png'],
  ]) {
    homeCards.push({ name, link: go(slug), logo: await img(rel, name) })
  }

  const baradise = await imgs([
    `${L}/BARADISE/EXTERIOR/Copia de 08.png`,
    `${L}/BARADISE/EXTERIOR/Copia de 10.png`,
    `${L}/BARADISE/EXTERIOR/Copia de 15.png`,
    `${L}/BARADISE/EXTERIOR/Copia de 18.png`,
    `${L}/BARADISE/EXTERIOR/Copia de 20.png`,
    `${L}/BARADISE/HALL DE INGRESO/Copia de 05.png`,
    `${L}/BARADISE/HALL DE INGRESO/Copia de 08.png`,
    `${L}/BARADISE/INTERIOR/Copia de 01.png`,
    `${L}/BARADISE/INTERIOR/Copia de 07.png`,
    `${L}/BARADISE/INTERIOR/Copia de 08.png`,
    `${L}/BARADISE/INTERIOR/Copia de 10.png`,
  ])
  const velerian = await imgs([
    `${L}/VELERIAN/EXTERIOR/Copia de 06.png`,
    `${L}/VELERIAN/EXTERIOR/Copia de 08.png`,
    `${L}/VELERIAN/EXTERIOR/Copia de Copia de IMG_2360.JPG`,
    'HOME/VELERIAN/Copia de 05.png',
    'HOME/VELERIAN/Copia de 07.png',
  ])
  const acros = await imgs(
    ['11', '12', '16', '19', '20'].map((n) => `${L}/BENROW/BENROW ACROS/Copia de ${n}.png`),
  )
  const vantier = await imgs([
    ...['11', '14', '17', '6', '1'].map((n) => `${L}/TIER/PROTAGÓNICO/EXTERIOR/Copia de ${n}.png`),
    ...['5', '6', '23'].map((n) => `${L}/TIER/PROTAGÓNICO/INTERIOR/Copia de ${n}.png`),
    ...['9', '5', '15', '16', '20', '8', '3'].map((n) => `${L}/TIER/VANTIER/Copia de ${n}.png`),
  ])
  const loftier = await imgs([
    `${L}/TIER/LOFTIER/Copia de 01.png`,
    `${L}/TIER/LOFTIER/Copia de 02.png`,
    `${L}/TIER/LOFTIER/Copia de Copia de @mathycorrea - 26.JPG`,
  ])
  const alarif = await imgs(['HOME/ALARIF/Copia de 1.png', 'HOME/ALARIF/Copia de 2.png'])
  const benrowHome = await imgs(['HOME/BENROW/Copia de 18.png', 'HOME/BENROW/Copia de 19.png'])

  return [
    {
      slug: 'home',
      title: 'Inicio',
      hero: { type: 'none' },
      layout: [
        {
          blockType: 'banner',
          image: portada,
          title: 'CONSTRUIMOS',
          titleAccent: 'tu',
          titleAfter: 'FUTURO',
          link: { type: 'custom', url: '/nosotros', label: 'Conocé a la empresa' },
        },
        { blockType: 'lineCards', title: 'NUESTRAS LINEAS EDILICIAS', cards: homeCards },
        {
          blockType: 'stats',
          stats: [
            { value: '+5.000', label: 'Propietarios' },
            { value: '+5.000', label: 'Inversores' },
            { value: '+500', label: 'Unidades entregadas' },
          ],
        },
        contact(false),
      ],
    },
    simple('nosotros', 'Nosotros', [
      content(heading('NOSOTROS'), para(nosotrosText), para('AGUICONS® construimos tu futuro.')),
      {
        blockType: 'team',
        title: 'EQUIPO',
        members: [
          {
            name: 'COMERCIAL',
            description:
              'Equipo que conecta nuestra oferta con las personas, construyendo una experiencia de venta exclusiva con nuestros clientes.',
          },
          {
            name: 'TÉCNICA',
            description:
              'Profesionales que aportan su conocimiento y experiencia para planificar, coordinar y hacer realidad cada proyecto.',
          },
          {
            name: 'ADMINISTRATIVA',
            description:
              'Un grupo humano que aporta organización y control, gestionando los recursos económicos y financieros que sostienen el crecimiento de nuestra empresa.',
          },
        ],
      },
      {
        blockType: 'cta',
        richText: lexical(heading('MÁS SERVICIOS', 'h3')),
        links: [
          {
            link: {
              type: 'custom',
              url: '/servicios-de-construccion',
              label: 'SERVICIOS DE CONSTRUCCIONES',
              appearance: 'outline',
            },
          },
          {
            link: {
              type: 'custom',
              url: '/movimientos-de-suelo',
              label: 'MOVIMIENTOS DE SUELO',
              appearance: 'outline',
            },
          },
        ],
      },
      contact(true),
    ]),
    simple('servicios-de-construccion', 'Servicios de Construcción', [
      content(heading('SERVICIOS DE CONSTRUCCIÓN'), ...servicios.map(para)),
      contact(true),
    ]),
    simple('movimientos-de-suelo', 'Movimientos de Suelo', [
      content(heading('MOVIMIENTO DE SUELO'), ...suelo.map(para)),
      contact(true),
    ]),
    simple('alquiler', 'Alquiler', [
      content(heading('ALQUILER · Unidades y Oficinas'), para(LOREM)),
      cards('DISPONIBLES PARA ALQUILER', [
        {
          name: 'LOCAL COMERCIAL',
          image: benrowHome[0],
          badgeTitle: 'ALQUILER',
          badgeText: 'Local Comercial',
          link: go('benrow'),
        },
        {
          name: 'UNIDADES',
          image: alarif[0],
          badgeTitle: 'ALQUILER',
          badgeText: 'Unidades',
          link: go('alarif'),
        },
        {
          name: 'OFICINAS',
          image: vantier[3],
          badgeTitle: 'ALQUILER',
          badgeText: 'Oficinas',
          link: go('tier'),
        },
      ]),
      carousel('CARRUSEL DE VISTAS', [...benrowHome, ...alarif, vantier[3]]),
      contact(true),
    ]),
    simple('politica-de-privacidad', 'Política de Privacidad', [
      content(heading('POLÍTICA DE PRIVACIDAD'), ...PRIVACY.flatMap(([h, t]) => [heading(h, 'h3'), para(t)])),
      contact(true),
    ]),
    simple('contacto', 'Contacto', [
      content(heading('CONVERSÁ CON NOSOTROS'), para('Dejanos tu consulta y te responderemos a la brevedad.')),
      { blockType: 'formBlock', form: formId, enableIntro: false },
      {
        blockType: 'projectFacts',
        title: 'DÓNDE ESTAMOS',
        facts: [
          { label: 'Dirección', value: 'Av. Antártida Argentina 876' },
          { label: 'Email', value: 'info@aguicons.com' },
          { label: 'Teléfono / WhatsApp', value: '+54 9 3764 630673' },
        ],
        address: 'Av. Antártida Argentina 876, Posadas, Misiones, Argentina',
      },
      contact(true),
    ]),
    simple('baradise', 'Baradise', [
      intro('Baradise', baradiseText, [
        'Piscina',
        'Solarium',
        'Gimnasio',
        'Sum',
        'Co-working',
        'Terraza Verde',
      ], true),
      carousel('CARRUSEL DE VISTAS', baradise),
      contact(true),
    ]),
    simple('velerian', 'Velerian', [
      intro('VELERIAN'),
      carousel('CARRUSEL DE VISTAS', velerian),
      contact(true),
    ]),
    simple('benrow', 'Benrow', [
      intro('BENROW'),
      cards('PROTAGÓNICO (ACROS)', [
        { name: 'BENROW ACROS', image: acros[2], link: go('benrow-acros') },
        {
          name: 'BENROW',
          image: benrowHome[0],
          badgeTitle: 'ALQUILER',
          badgeText: 'Local Comercial',
          link: go('alquiler'),
        },
      ]),
      contact(true),
    ]),
    simple('benrow-acros', 'Benrow Acros', [
      intro('BENROW ACROS'),
      carousel('CARRUSEL DE VISTAS', acros),
      contact(true),
    ]),
    simple('tier', 'Tier', [
      intro('TIER'),
      cards('PROTAGÓNICO (VANTIER)', [
        { name: 'VANTIER', image: vantier[3], link: go('vantier') },
        { name: 'LOFTIER', image: loftier[0], link: go('loftier') },
      ]),
      contact(true),
    ]),
    simple('vantier', 'Vantier', [
      intro('VANTIER'),
      carousel('CARRUSEL DE VISTAS', vantier),
      contact(true),
    ]),
    simple('loftier', 'Loftier', [
      intro('LOFTIER'),
      carousel('CARRUSEL DE VISTAS', loftier),
      contact(true),
    ]),
    simple('alarif', 'Alarif', [
      intro('ALARIF'),
      cards('PROTAGÓNICO (RIVÁ)', [
        {
          name: 'ALARIF FAZARA',
          image: alarif[0],
          badgeTitle: 'ALQUILER',
          badgeText: 'Unidades',
          link: go('alarif-fazara'),
        },
        {
          name: 'ALARIF TRENCH',
          image: alarif[1],
          badgeTitle: 'ALQUILER',
          badgeText: 'Unidades',
          link: go('alarif-trench'),
        },
        { name: 'ALARIF', image: alarif[0], link: go('alarif') },
      ]),
      contact(true),
    ]),
    simple('alarif-fazara', 'Alarif Fazara', [
      intro('ALARIF FAZARA'),
      carousel('CARRUSEL DE VISTAS', alarif),
      contact(true),
    ]),
    simple('alarif-trench', 'Alarif Trench', [
      intro('ALARIF TRENCH'),
      carousel('CARRUSEL DE VISTAS', alarif),
      contact(true),
    ]),
  ]
}

const META: Record<string, string> = {
  home: 'Aguicons, desarrolladora de Posadas, Misiones. Líneas edilicias de inversión y para vivir.',
  nosotros: 'Más de 15 años cambiando el skyline de las ciudades. Conocé al equipo de Aguicons.',
  'servicios-de-construccion':
    'Soluciones integrales de construcción para el sector público y privado.',
  'movimientos-de-suelo': 'Excavación, apertura de calles, demoliciones y adecuación de terrenos.',
  alquiler: 'Unidades, oficinas y locales comerciales en alquiler en las líneas de Aguicons.',
  contacto: 'Contactá a Aguicons: info@aguicons.com, Av. Antártida Argentina 876, Posadas.',
  baradise: 'Baradise: lujo, arquitectura orgánica y diseño contemporáneo frente al río.',
}
const LINE_PAGES = ['baradise', 'velerian', 'benrow', 'tier', 'alarif']
const LINE_LABEL: Record<string, string> = {
  baradise: 'Baradise',
  velerian: 'Velerian',
  benrow: 'Benrow',
  tier: 'Tier',
  alarif: 'Alarif',
}

/** Metadatos SEO, fondo y accesos a otras líneas en cada página. */
const finish = (pages: any[]) =>
  pages.map((p) => {
    const root = LINE_PAGES.find((l) => p.slug === l || p.slug.startsWith(`${l}-`))
    const layout = [...p.layout]
    const leads = (project: string) => [
      { blockType: 'inquiry', project, form: inquiryFormId },
    ]
    if (root) {
      layout.splice(layout.length - 1, 0, ...[
        {
          blockType: 'projectFacts',
          title: 'FICHA TÉCNICA',
          facts: [
            { label: 'Ubicación', value: 'A confirmar' },
            { label: 'Superficies', value: 'A confirmar' },
            { label: 'Unidades', value: 'A confirmar' },
            { label: 'Entrega', value: 'A confirmar' },
          ],
        },
        { blockType: 'brochure', project: p.title, form: brochureFormId },
        ...leads(p.title),
      ])
      layout.splice(layout.length - 1, 0, {
        blockType: 'lineLinks',
        title: 'OTRAS LÍNEAS',
        links: LINE_PAGES.filter((l) => l !== root).map((l) => ({
          label: LINE_LABEL[l],
          url: `/${l}`,
        })),
      })
    }
    if (['servicios-de-construccion', 'movimientos-de-suelo', 'alquiler'].includes(p.slug)) {
      layout.splice(layout.length - 1, 0, { blockType: 'inquiry', project: p.title, form: inquiryFormId })
    }
    const description =
      META[p.slug] ||
      (root ? `${p.title}: una línea edilicia de Aguicons. Conocé sus vistas y contactanos.` : '')
    return {
      ...p,
      layout,
      background: p.slug === 'nosotros' ? 'gold' : 'default',
      meta: { title: p.title, description },
    }
  })

const run = async () => {
  const payload = await getPayload({ config })
  payloadRef = payload

  if (process.env.RESET_MEDIA) {
    const all = await payload.find({ collection: 'media', limit: 1000, depth: 0, pagination: false })
    for (const d of all.docs as any[]) await payload.delete({ collection: 'media', id: d.id })
    payload.logger.info(`media borrada: ${all.docs.length}`)
  }

  const notify = process.env.NOTIFY_EMAIL || 'info@aguicons.com'
  const emails = (subject: string) => [
    {
      emailFrom: '"Aguicons Web" <no-reply@aguicons.com>',
      emailTo: notify,
      replyTo: '{{email}}',
      subject,
      message: lexical(para('Nueva consulta recibida desde el sitio web:'), para('{{*:table}}')),
    },
  ]
  const text = (name: string, label: string, required = true, blockType = 'text') => ({
    name,
    blockName: name,
    blockType,
    label,
    required,
    width: 100,
  })
  const upsertForm = async (title: string, fields: any[], subject: string, thanks: string, button: string) => {
    const data = {
      title,
      submitButtonLabel: button,
      confirmationType: 'message',
      confirmationMessage: lexical(para(thanks)),
      fields,
      emails: emails(subject),
    } as any
    const found = await payload.find({ collection: 'forms', where: { title: { equals: title } }, limit: 1 })
    if (found.docs[0]) {
      await payload.update({ collection: 'forms', id: found.docs[0].id, data })
      return found.docs[0].id
    }
    return (await payload.create({ collection: 'forms', data })).id
  }
  formId = await upsertForm(
    'Contacto',
    [
      text('nombre', 'Nombre y apellido'),
      text('email', 'Email', true, 'email'),
      text('telefono', 'Teléfono', false),
      text('mensaje', 'Mensaje', true, 'textarea'),
    ],
    'Nueva consulta desde la web (Contacto)',
    '¡Gracias! Recibimos tu consulta y te responderemos a la brevedad.',
    'Enviar',
  )
  inquiryFormId = await upsertForm(
    'Consulta por proyecto',
    [
      text('proyecto', 'Proyecto', false),
      text('tipo', 'Tipo', false),
      text('nombre', 'Nombre y apellido'),
      text('email', 'Email', true, 'email'),
      text('telefono', 'Teléfono', false),
      text('mensaje', 'Mensaje', false, 'textarea'),
    ],
    'Nueva consulta por proyecto desde la web',
    '¡Gracias! Un asesor se comunicará con vos.',
    'Enviar consulta',
  )
  brochureFormId = await upsertForm(
    'Descarga de brochure',
    [
      text('proyecto', 'Proyecto', false),
      text('tipo', 'Tipo', false),
      text('nombre', 'Nombre y apellido'),
      text('email', 'Email', true, 'email'),
    ],
    'Solicitud de brochure desde la web',
    '¡Gracias! Te enviaremos el brochure.',
    'Recibir brochure',
  )

  const pages = finish(await buildPages())

  const empties = await payload.find({ collection: 'pages', where: { title: { exists: false } }, limit: 50, draft: true })
  for (const d of empties.docs) await payload.delete({ collection: 'pages', id: d.id, context: { disableRevalidate: true } })

  for (const p of pages) {
    const data = { ...p, _status: 'published', publishedAt: new Date().toISOString() }
    const existing = await payload.find({
      collection: 'pages',
      where: { slug: { equals: p.slug } },
      limit: 1,
      draft: true,
    })
    if (existing.docs[0]) {
      await payload.update({ collection: 'pages', id: existing.docs[0].id, data, context: { disableRevalidate: true } })
      payload.logger.info(`actualizada: ${p.slug}`)
    } else {
      await payload.create({ collection: 'pages', data, context: { disableRevalidate: true } })
      payload.logger.info(`creada: ${p.slug}`)
    }
  }

  await payload.updateGlobal({
    slug: 'header',
    data: {
      navItems: [
        { link: { type: 'custom', url: '/', label: 'Inicio' } },
        { link: { type: 'custom', url: '/nosotros', label: 'Nosotros' } },
        { link: { type: 'custom', url: '/servicios-de-construccion', label: 'Servicios' } },
        { link: { type: 'custom', url: '/movimientos-de-suelo', label: 'Movimientos de suelo' } },
        { link: { type: 'custom', url: '/alquiler', label: 'Alquiler' } },
        { link: { type: 'custom', url: '/contacto', label: 'Contacto' } },
      ],
    },
    context: { disableRevalidate: true },
  })
  await payload.updateGlobal({
    slug: 'footer',
    data: {
      copyright: 'Copyright © 2026 Aguicons',
      whatsapp: '5493764630673',
      navItems: [{ link: { type: 'custom', url: '/politica-de-privacidad', label: 'Política de privacidad' } }],
    },
    context: { disableRevalidate: true },
  })

  payload.logger.info('listo')
  process.exit(0)
}

run().catch((e) => {
  console.error(e)
  process.exit(1)
})
