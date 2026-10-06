# Aguicons en WordPress – Guía de gestión

Panel: `tu-dominio.com/wp-admin`. Todo se edita desde ahí, sin tocar código.

## Instalar en Ferozo (una sola vez)
1. En el panel de Ferozo, instalar WordPress en el dominio.
2. En WordPress: **Apariencia → Temas → Añadir nuevo → Subir tema** → subir `aguicons.zip` → **Activar**.
   (Si el hosting limita el tamaño de subida, descomprimir `aguicons.zip` y subir la carpeta `aguicons` por FTP a `wp-content/themes/`.)
3. Ir a **Herramientas → Aguicons: contenido inicial** → **Cargar contenido inicial**. Sube las 49 fotos y crea páginas, líneas, menú y textos (tarda un par de minutos, se ve el avance).
4. **Ajustes → Aguicons**: revisar WhatsApp, email que recibe las consultas, dirección y (opcional) ID de Google Analytics.
5. **Ajustes → Generales**: confirmar el título y la dirección del sitio.

## ¿Dónde cambio cada cosa?
| Quiero cambiar… | Dónde |
|---|---|
| Texto, fotos, logo, amenities de una línea (Baradise, Tier…) | **Líneas edilicias** → la línea. Título + texto = cabecera negra; *Imagen destacada* = foto de la tarjeta |
| Fotos del carrusel de una línea | Línea → cuadro **Galería** (agregar, quitar, arrastrar para ordenar) |
| Estado, ficha técnica, mapa, PDF del brochure | Línea → cuadro **Ficha técnica, estado y mapa** |
| Agregar un sub-proyecto (ej: Benrow Acros) | **Líneas edilicias → Agregar**, y en *Atributos* elegir **Superior** = la línea principal |
| Orden de las líneas en el carrusel de inicio | *Atributos → Orden* (número menor = primero) |
| Cartel dorado “ALQUILER – Unidades” (y que aparezca en Alquiler) | Línea → cuadro lateral **Carrusel de inicio y cartel** |
| Portada del inicio (foto, título, botón), estadísticas, WhatsApp, emails, copyright | **Ajustes → Aguicons** |
| Texto de Nosotros, Servicios, Movimiento de suelo, Alquiler, Contacto, Privacidad | **Páginas** → la página (editor normal) |
| Fondo dorado en una página | Página → cuadro lateral **Fondo de la página** |
| Menú superior y del pie | **Apariencia → Menús** |
| Testimonios | **Testimonios → Agregar** (título = nombre, texto = opinión, foto = imagen destacada) |
| Consultas y descargas de brochure recibidas | **Consultas** |
| Título/descripción para Google | Se toman del título y el *extracto* de cada página/línea (o instalar Yoast/Rank Math, el tema los respeta) |

## Elementos que se pueden insertar en cualquier página (shortcodes)
Escribirlos en un bloque “Shortcode” del editor:
- `[aguicons_contacto_form]` formulario de contacto
- `[aguicons_consulta proyecto="Nombre"]` formulario “Quiero información de…”
- `[aguicons_brochure proyecto="Nombre"]` formulario de brochure
- `[aguicons_mapa titulo="DÓNDE ESTAMOS" direccion="…"]` ficha con mapa (sin dirección usa la de Ajustes)
- `[aguicons_alquiler]` tarjetas y carrusel de todo lo que tiene cartel de alquiler
- `[aguicons_lineas]` carrusel de líneas · `[aguicons_estadisticas]` · `[aguicons_testimonios]` · `[aguicons_contacto]` barra de contacto

## Avisos por email
Las consultas llegan al email de **Ajustes → Aguicons** y además quedan guardadas en **Consultas**.
Si los emails no llegan o caen en spam, instalar el plugin gratuito **WP Mail SMTP** y configurar una casilla del dominio (es lo único que se recomienda instalar).

## Analytics y cookies
Cargar el ID `G-XXXX` en **Ajustes → Aguicons**. Solo se activa si el visitante acepta el aviso de cookies. Se registran consultas, descargas de brochure y clics en WhatsApp.

## Pendientes de contenido
Textos de Velerian, Benrow, Tier y Alarif (hoy lorem ipsum), datos de la ficha técnica (hoy “A confirmar”), logos de cada línea, fotos del equipo, PDF de brochure y testimonios reales. La política de privacidad es un texto base: conviene revisión legal.

## Funciones nuevas (cuadro “Unidades, avance de obra, video y ubicación” en cada línea)
- **Unidades disponibles:** una por línea con el formato `Tipología | Superficie | Precio | Estado` (se puede pegar desde Excel). Se muestra una tabla con filtros por tipología y estado y orden por superficie. Sin datos, la sección no aparece.
- **Avance de obra:** botón “Agregar entrada” (fecha, texto, fotos). Se ve como línea de tiempo con fotos ampliables.
- **Video o recorrido 360°:** pegar el enlace de YouTube, Vimeo, Matterport o un `.mp4`.
- **Mapa de proyectos:** cargar *Latitud, longitud* en cada línea (en Google Maps: clic derecho sobre el lugar y copiar los números). Cuando alguna línea tiene coordenadas, aparece el mapa con un pin por proyecto en el inicio. También se puede insertar con `[aguicons_mapa_proyectos]`.
- **Preguntas frecuentes:** menú **Preguntas frecuentes → Agregar** (título = pregunta, texto = respuesta). Se muestran con el código `[aguicons_faq]` y generan datos estructurados para Google.
- **Invertir:** se creó la página **Invertir** como *borrador*. Completarla (modalidad de compra, financiación) y publicarla; ya incluye las preguntas frecuentes y el formulario de consulta.
- **Novedades (blog):** menú **Entradas → Añadir nueva**. Aparecen en la página Novedades (ya está en el menú).

## Velocidad y seguridad (ya incluidas en el tema)
Sin emojis ni enlaces innecesarios, precarga de fuentes, imágenes con carga diferida, XML-RPC desactivado, lista de usuarios oculta a visitantes y mensaje de acceso genérico.
**Recomendado instalar en Ferozo (gratuitos):** un plugin de caché (LiteSpeed Cache, WP Super Cache o similar), uno de copias automáticas (UpdraftPlus) y uno de protección de acceso (Limit Login Attempts Reloaded o Wordfence). Activar HTTPS y mantener WordPress al día.

## Panel de gestión “Aguicons” (menú propio, recomendado para el uso diario)
En el menú lateral de WordPress aparece **Aguicons** con:
- **Panel:** resumen (líneas, consultas nuevas) y una tabla con el **% completo de cada línea** y qué le falta, con el botón *Completar*.
- **➕ Nueva línea (asistente):** 5 pasos (datos, textos, imágenes, ficha y extras, publicar). Guarda como borrador o publica; al final muestra lo que falta y el enlace para ver la página. También sirve para **completar o editar** una línea existente (*Completar* en el Panel o *Asistente* en la lista de líneas).
- **Líneas edilicias:** la lista con acciones **Asistente, Duplicar y Pausar/Publicar** (pausar oculta la línea sin borrarla).
- **Estructura del sitio:** portada, orden y visibilidad del carrusel de líneas, estadísticas, menú principal y datos del pie, en una sola pantalla.
- **Consultas:** filtros por estado, proyecto y tipo; marcar **Atendida/Nueva**; **exportar a Excel (CSV)**.
- **Contenido:** accesos a testimonios, preguntas frecuentes, novedades, páginas y medios.
- **Ajustes** y **Ayuda** (con formatos y ejemplos de cada campo).

## Carruseles de Construcción y Movimiento de suelo
En **Aguicons → Estructura del sitio → Carruseles de servicios** se cargan, ordenan y quitan las fotos de cada página de servicio (también se pueden editar desde **Páginas → [página] → Carrusel de imágenes**). Las fotos de Movimiento de suelo vienen cargadas; para Construcción (obras) hay que subir las fotos. Si una página no tiene fotos, el carrusel no se muestra. En **Otros servicios** se muestran ambos carruseles junto a cada descripción.
