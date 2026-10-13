# Guía para publicar el sitio nuevo de Aguicons en Ferozo

Situación actual: `aguicons.com` ya tiene un sitio publicado (WordPress en `/public_html/`). El sitio nuevo **no se instala encima**: primero se prueba aparte y recién cuando está aprobado se hace el cambio.

Archivo que se sube: `wordpress/aguicons.zip` (el tema completo, unos 8 MB).

> Las contraseñas se escriben solo en el panel de Ferozo o en el programa de FTP. No las compartas en chats ni mails.

---

## Paso 0 — Qué tener a mano

- Usuario y contraseña del panel de Ferozo.
- Programa de FTP (FileZilla, gratis): https://filezilla-project.org
- Datos de FTP: servidor `c2761204.ferozo.com`, usuario `c2761204`, puerto 21.
- El archivo `aguicons.zip` y la carpeta `wordpress/aguicons`.

---

## Paso 1 — Copia de seguridad del sitio actual (obligatorio)

Hacela **antes de tocar nada**. Con ella se puede volver atrás siempre.

1. Abrí FileZilla y conectate con los datos de FTP.
2. En el lado derecho (servidor), entrá a la carpeta `/public_html/`.
3. Seleccioná todo su contenido y arrastralo a una carpeta nueva de tu PC, por ejemplo `Copia sitio actual 2026-10`.
4. Esperá a que termine (puede tardar). Verificá que no haya errores en la pestaña "Transferencias fallidas".
5. Además, en el panel de Ferozo (sección Wordpress) fijate si hay un botón de **copia de seguridad / backup** de la base de datos y usalo. La base de datos guarda páginas, textos y configuración, y el FTP solo copia archivos.

---

## Paso 2 — Instalar un WordPress de prueba aparte

1. En Ferozo entrá a **Wordpress** → botón naranja **Instalar WordPress**.
2. Elegí el dominio `aguicons.com`.
3. En **Directorio** poné `nuevo` (queda en `aguicons.com/nuevo`). **No lo dejes vacío**: vacío instala sobre el sitio actual.
4. Elegí un usuario y una contraseña de administrador **propios y seguros**.
5. Instalá. Al terminar, el sitio actual sigue igual y el de prueba queda en `https://aguicons.com/nuevo`.

---

## Paso 3 — Subir el tema

**Opción A (la más simple): desde WordPress**

1. Entrá a `https://aguicons.com/nuevo/wp-admin`.
2. Apariencia → Temas → Añadir nuevo → Subir tema.
3. Elegí `aguicons.zip` → Instalar → **Activar**.

**Opción B: por FTP** (si la opción A falla por el tamaño)

1. En FileZilla entrá a `/public_html/nuevo/wp-content/themes/`.
2. Subí la carpeta `aguicons` (descomprimida) entera.
3. En WordPress: Apariencia → Temas → activá "Aguicons".

---

## Paso 4 — Cargar el contenido inicial

1. En WordPress: **Herramientas → "Aguicons: contenido inicial"**.
2. Apretá el botón y esperá. Sube unas 50 fotos y crea páginas, líneas y menús. **No cierres la pestaña** hasta que diga que terminó.
3. Si se corta, volvé a entrar y apretá de nuevo: las fotos ya subidas no se repiten.

---

## Paso 5 — Configurar desde el panel "Aguicons"

En el menú izquierdo de WordPress, **Aguicons**:

- **Ajustes:** WhatsApp (`5493765009075`), teléfono, email que recibe las consultas, ID de Google Analytics (si lo tienen), dirección.
- **Estructura del sitio:** revisá portada, orden de las líneas, menú y pie.
- **Contenido → Líneas edilicias:** revisá textos y fotos de cada línea.

---

## Paso 6 — Que lleguen los mails de las consultas

El formulario de contacto envía un mail a la casilla configurada. En el hosting hay que comprobar que salga.

1. Mandá una consulta de prueba desde la página de Contacto.
2. Revisá que llegue a `info@aguicons.com` (mirá también la carpeta de correo no deseado).
3. Si no llega, hay que configurar el envío (SMTP) con una de las cuentas de correo del dominio. Avisá y se prepara.

Las consultas quedan siempre guardadas en **Aguicons → Consultas**, aunque el mail falle.

---

## Paso 7 — Revisión final antes de publicar

- [ ] Inicio, Nosotros, Otros servicios, Alquiler y Contacto se ven bien en computadora y celular.
- [ ] Los botones de WhatsApp abren el número correcto.
- [ ] El formulario de contacto envía y llega el mail.
- [ ] Las fotos de cada línea cargan y el carrusel funciona.
- [ ] La política de privacidad fue revisada por quien corresponda.
- [ ] La contraseña de administrador es propia y segura (nunca la de prueba).

---

## Paso 8 — El cambio al dominio real (cuando esté aprobado)

Decidilo con el sitio nuevo ya probado. Hay dos formas:

**Forma 1 — Reemplazar el diseño en el WordPress actual (más simple)**
1. Hacé de nuevo una copia de seguridad (Paso 1).
2. En el WordPress de `aguicons.com`: subí y **activá** el tema Aguicons (Paso 3) y cargá el contenido (Paso 4).
3. Configurá los Ajustes (Paso 5).
4. El diseño anterior deja de verse. Si hay que volver atrás, se reactiva el tema anterior y se restaura la copia.

**Forma 2 — Mover el sitio de prueba a la raíz**
Se hace con un plugin de migración (por ejemplo *All-in-One WP Migration* o *Duplicator*): se exporta `/nuevo` y se importa en `aguicons.com`. Conserva exactamente lo probado, pero requiere más cuidado con las direcciones (URLs).

---

## Si algo sale mal

- Con la copia del Paso 1 se vuelve al estado anterior.
- No borres la carpeta `/public_html/` ni la base de datos sin tener la copia.
- Si no estás seguro de un paso, pará y consultá antes de seguir.
