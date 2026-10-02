# Guía rápida del CMS (Aguicons)

Panel: `http://localhost:3000/admin` → **Páginas**. Cada página se arma con bloques que se pueden reordenar arrastrando.

## Bloques disponibles
| Bloque | Para qué sirve |
|---|---|
| Banner de portada | Cabecera del inicio: foto, título ("CONSTRUIMOS *tu* FUTURO") y botón |
| Líneas Edilicias | Carrusel de tarjetas con foto (cada una enlaza a su línea) |
| Ficha de Proyecto (fondo negro) | Cabecera de cada línea: logo/nombre, texto y amenities. Casilla "tipografía decorativa" para el estilo Baradise |
| Carrusel de Vistas | Galería de fotos (se arrastra, avanza con el mouse y abre en grande al hacer click) |
| Tarjetas protagónicas | Tarjetas con foto y cartel dorado (ej: ALQUILER – Local Comercial) |
| Otras líneas (accesos) | Botones a las demás líneas, al final de cada línea |
| Equipo / Estadísticas / Barra de Contacto / Formulario | Resto de secciones |

## Tareas comunes
- **Cambiar o reordenar fotos de un carrusel:** abrir la página → bloque "Carrusel de Vistas" → arrastrar filas o reemplazar la imagen.
- **Subir imágenes nuevas:** menú **Multimedia**. Se generan solas versiones WebP livianas; completar el campo *alt* (texto alternativo).
- **Logo real de una línea:** en "Ficha de Proyecto" cargar el campo *Logo*; reemplaza al nombre en texto.
- **Fondo dorado en una página:** barra lateral de la página → *Fondo de la página*.
- **WhatsApp y copyright:** **Pie de Página** (WhatsApp solo números, ej. 5493764630673).
- **Menú:** **Cabecera** → items.
- **SEO:** pestaña *SEO* de cada página (título y descripción).
- **Consultas recibidas:** **Formularios → Envíos**.

## Reaplicar el contenido base
`npx tsx --env-file=.env scripts/seed-aguicons.ts` vuelve a cargar páginas, textos e imágenes de `Documentos/IMAGENES`
(los cambios hechos a mano en esas páginas se sobrescriben). Con `RESET_MEDIA=1` también se vuelven a subir las imágenes.

## Consultas, brochure y avisos por email
- Cada línea tiene **Descarga de brochure** y **Consulta por proyecto** (bloques en la página). Cada envío queda en **Formularios → Envíos** con el campo *proyecto*.
- **Aviso por email:** las consultas se envían a `NOTIFY_EMAIL` (por defecto `info@aguicons.com`; el destinatario también se cambia en **Formularios → [formulario] → Emails**). Para que el email salga de verdad hay que completar `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS` en el `.env` (ver `.env.example`); sin eso solo se muestra en la consola del servidor.
- **Brochure en PDF:** en el bloque "Descarga de brochure" subir el PDF en el campo *Archivo*. Si está vacío, al enviar se avisa que el brochure llegará por email.
- **WhatsApp:** el mensaje cambia según la página (ej. "Hola, quiero más información sobre Tier").

## Ficha técnica, estado y mapa
Bloque **Ficha técnica + mapa** en cada línea: elegir *Estado* (En pozo / En construcción / Entregado / Últimas unidades), editar los datos (hoy dicen "A confirmar") y completar *Dirección o lugar para el mapa*.

## Testimonios
Bloque **Testimonios**: agregarlo a la página que corresponda y cargar solo opiniones reales y autorizadas. Sin testimonios cargados, el bloque no se muestra.

## Analytics y privacidad
- **Google Analytics 4:** poner `NEXT_PUBLIC_GA_ID=G-XXXX` en el `.env`. Solo se carga si el visitante acepta el aviso de cookies. Se registran los eventos `generate_lead` (consultas y brochures), `file_download` y `whatsapp_click`.
- **Política de privacidad:** página `/politica-de-privacidad` (enlazada en el pie y en los formularios). Es un texto base: conviene que lo revise un asesor legal.
