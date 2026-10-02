<?php
/**
 * Contenido inicial: sube las fotos a la biblioteca de medios, crea las páginas, las líneas y los menús.
 * Se ejecuta desde Herramientas → Aguicons: contenido inicial (por pasos, para no agotar el tiempo del hosting).
 */

if (!defined('ABSPATH')) {
    exit;
}

const AGUI_LOREM = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.';

add_action('admin_menu', function () {
    add_management_page('Aguicons: contenido inicial', 'Aguicons: contenido inicial', 'manage_options', 'aguicons-import', function () {
        $manifest = agui_manifest();
        ?>
        <div class="wrap">
            <h1>Aguicons – Contenido inicial</h1>
            <p>Carga las <?php echo count($manifest); ?> fotos en la biblioteca de medios y crea las páginas, las líneas edilicias, el menú y los textos del sitio.
                Es seguro repetirlo: no duplica lo que ya existe, pero <strong>sí restablece los textos de las páginas iniciales</strong> a su versión original.</p>
            <p><button type="button" class="button button-primary button-hero" id="agui-import-start">Cargar contenido inicial</button></p>
            <progress id="agui-import-progress" value="0" max="1"></progress>
            <ul id="agui-import-log"></ul>
        </div>
        <?php
    });
});

add_action('admin_notices', function () {
    if (get_option('agui_imported') || !current_user_can('manage_options')) {
        return;
    }
    $screen = get_current_screen();
    if ($screen && $screen->id === 'tools_page_aguicons-import') {
        return;
    }
    printf(
        '<div class="notice notice-info"><p><strong>Aguicons:</strong> falta cargar el contenido inicial del sitio (fotos, páginas y líneas). <a class="button button-primary" href="%s">Cargar ahora</a></p></div>',
        esc_url(admin_url('tools.php?page=aguicons-import'))
    );
});

function agui_manifest(): array
{
    $file = get_theme_file_path('assets/seed/manifest.json');
    $data = file_exists($file) ? json_decode((string) file_get_contents($file), true) : [];
    return is_array($data) ? $data : [];
}

add_action('wp_ajax_agui_import_step', function () {
    check_ajax_referer('agui_import', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permiso', 403);
    }
    @set_time_limit(180);
    wp_raise_memory_limit('image');

    $step = sanitize_key($_POST['step'] ?? 'images');
    $index = (int) ($_POST['index'] ?? 0);
    $keys = array_keys(agui_manifest());
    $total = count($keys) + 1;

    try {
        if ($step === 'images') {
            if (!isset($keys[$index])) {
                wp_send_json_success(['label' => 'Fotos listas', 'done' => $index, 'total' => $total, 'next' => ['step' => 'content', 'index' => 0]]);
            }
            agui_import_image($keys[$index]);
            $next = $index + 1 < count($keys) ? ['step' => 'images', 'index' => $index + 1] : ['step' => 'content', 'index' => 0];
            wp_send_json_success(['label' => 'Foto ' . ($index + 1) . ' de ' . count($keys) . ': ' . basename($keys[$index]), 'done' => $index + 1, 'total' => $total, 'next' => $next]);
        }
        agui_import_content();
        wp_send_json_success(['label' => 'Páginas, líneas y menús creados', 'done' => $total, 'total' => $total, 'next' => null]);
    } catch (Throwable $e) {
        wp_send_json_error($e->getMessage());
    }
});

/** Sube una foto del tema a la biblioteca (una sola vez) y devuelve su ID. */
function agui_import_image(string $rel): int
{
    $map = get_option('agui_seed_map', []);
    if (!empty($map[$rel]) && get_post($map[$rel])) {
        return (int) $map[$rel];
    }
    $manifest = agui_manifest();
    $src = get_theme_file_path('assets/seed/' . ($manifest[$rel] ?? ''));
    if (empty($manifest[$rel]) || !file_exists($src)) {
        throw new RuntimeException('No se encontró la imagen: ' . $rel);
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam($manifest[$rel]);
    copy($src, $tmp);
    $id = media_handle_sideload(['name' => $manifest[$rel], 'tmp_name' => $tmp], 0);
    if (is_wp_error($id)) {
        @unlink($tmp);
        throw new RuntimeException($id->get_error_message());
    }
    $parts = array_filter(explode('/', dirname($rel)), fn($s) => !preg_match('/^(HOME|LINEAS EDILICIAS|\d+\. )/', $s));
    $alt = $parts ? 'Aguicons - ' . implode(' - ', array_map(fn($s) => ucfirst(mb_strtolower($s)), $parts)) : 'Aguicons';
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    $map[$rel] = $id;
    update_option('agui_seed_map', $map, false);
    return (int) $id;
}

function agui_seed_id(string $rel): int
{
    $map = get_option('agui_seed_map', []);
    return (int) ($map[$rel] ?? 0);
}

/* ---------------------------------------------------------------------------------------------
 * Marcado de bloques para las páginas
 * ------------------------------------------------------------------------------------------- */

function agui_b_heading(string $t, int $lvl = 2, bool $center = false): string
{
    $a = '{"level":' . $lvl . ($center ? ',"textAlign":"center"' : '') . '}';
    return '<!-- wp:heading ' . $a . ' --><h' . $lvl . ' class="wp-block-heading' . ($center ? ' has-text-align-center' : '') . '">' . esc_html($t) . '</h' . $lvl . '><!-- /wp:heading -->' . "\n\n";
}

function agui_b_para(string $t, bool $center = false): string
{
    return '<!-- wp:paragraph' . ($center ? ' {"align":"center"}' : '') . ' --><p' . ($center ? ' class="has-text-align-center"' : '') . '>' . esc_html($t) . '</p><!-- /wp:paragraph -->' . "\n\n";
}

function agui_b_shortcode(string $sc): string
{
    return '<!-- wp:shortcode -->' . $sc . '<!-- /wp:shortcode -->' . "\n\n";
}

function agui_b_team(array $members): string
{
    $o = '<!-- wp:columns {"className":"agui-team"} --><div class="wp-block-columns agui-team">';
    foreach ($members as [$name, $text]) {
        $o .= '<!-- wp:column --><div class="wp-block-column">';
        $o .= '<!-- wp:group {"backgroundColor":"white","layout":{"type":"constrained"}} --><div class="wp-block-group has-white-background-color has-background"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">' . esc_html($name) . '</h3><!-- /wp:heading --></div><!-- /wp:group -->';
        $o .= '<!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size">' . esc_html($text) . '</p><!-- /wp:paragraph -->';
        $o .= '</div><!-- /wp:column -->';
    }
    return $o . '</div><!-- /wp:columns -->' . "\n\n";
}

function agui_b_buttons(array $btns): string
{
    $o = '<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-buttons">';
    foreach ($btns as [$label, $url]) {
        $o .= '<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url($url) . '">' . esc_html($label) . '</a></div><!-- /wp:button -->';
    }
    return $o . '</div><!-- /wp:buttons -->' . "\n\n";
}

/** Crea o actualiza (por slug) una página. */
function agui_upsert_page(string $slug, string $title, string $content, array $meta = [], string $excerpt = ''): int
{
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $args = ['post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content, 'post_excerpt' => $excerpt];
    if ($existing) {
        $args['ID'] = $existing->ID;
        $id = wp_update_post($args);
    } else {
        $id = wp_insert_post($args);
    }
    foreach ($meta as $k => $v) {
        update_post_meta($id, $k, $v);
    }
    return (int) $id;
}

/** Crea o actualiza una línea / sub-proyecto. */
function agui_upsert_line(array $d): int
{
    $parent = $d['parent'] ?? 0;
    $existing = get_posts(['post_type' => 'linea', 'name' => $d['slug'], 'post_parent' => $parent, 'numberposts' => 1, 'post_status' => 'any']);
    $paras = array_filter(array_map('trim', preg_split("/\n\s*\n/", $d['text'] ?? AGUI_LOREM)));
    $content = '';
    foreach ($paras as $p) {
        $content .= agui_b_para($p);
    }
    $args = [
        'post_type' => 'linea', 'post_status' => 'publish', 'post_name' => $d['slug'], 'post_title' => $d['title'],
        'post_content' => $content, 'post_parent' => $parent, 'menu_order' => $d['order'] ?? 0,
        'post_excerpt' => wp_trim_words(implode(' ', $paras), 28),
    ];
    if ($existing) {
        $args['ID'] = $existing[0]->ID;
        $id = wp_update_post($args);
    } else {
        $id = wp_insert_post($args);
    }
    if (!empty($d['thumb'])) {
        set_post_thumbnail($id, agui_seed_id($d['thumb']));
    }
    $gallery = array_filter(array_map('agui_seed_id', $d['gallery'] ?? []));
    $meta = [
        'script_title' => !empty($d['script']) ? '1' : '0',
        'logo_id' => !empty($d['logo']) ? agui_seed_id($d['logo']) : '',
        'amenities' => implode("\n", $d['amenities'] ?? []),
        'cards_title' => $d['cards_title'] ?? '',
        'gallery' => implode(',', $gallery),
        'facts' => "Ubicación: A confirmar\nSuperficies: A confirmar\nUnidades: A confirmar\nEntrega: A confirmar",
        'status' => '',
        'address' => '',
        'badge_title' => $d['badge_title'] ?? '',
        'badge_text' => $d['badge_text'] ?? '',
        'show_home' => $parent ? '0' : '1',
    ];
    foreach ($meta as $k => $v) {
        // No pisa lo que ya se haya editado a mano.
        if (!$existing || get_post_meta($id, '_agui_' . $k, true) === '') {
            update_post_meta($id, '_agui_' . $k, $v);
        }
    }
    return (int) $id;
}

function agui_import_content(): void
{
    $L = 'LINEAS EDILICIAS';
    $nums = fn(string $dir, array $n) => array_map(fn($x) => "$L/$dir/Copia de $x.png", $n);

    // Limpieza de lo que trae WordPress por defecto
    foreach (['hello-world' => 'post', 'sample-page' => 'page', 'pagina-de-ejemplo' => 'page', 'hola-mundo' => 'post'] as $slug => $type) {
        $p = get_page_by_path($slug, OBJECT, $type);
        if ($p) {
            wp_delete_post($p->ID, true);
        }
    }
    if (in_array(get_option('blogname'), ['', 'WordPress', 'Sitio WordPress', 'My WordPress Site'], true) || get_option('blogname') === 'AGUICONS') {
        update_option('blogname', 'AGUICONS');
        update_option('blogdescription', 'Construimos tu futuro');
    }

    // Ajustes del tema
    $opts = get_option('aguicons_opts', []);
    $opts = is_array($opts) ? $opts : [];
    if (empty($opts['hero_image'])) {
        $opts['hero_image'] = agui_seed_id('PORTADA/Portada Baradise.png');
    }
    update_option('aguicons_opts', $opts);

    // Páginas
    $nosotros = 'En AGUICONS® cambiamos el skyline de las ciudades. Y comenzamos por la nuestra, en Posadas, Misiones, Argentina, hace más de 15 años. Ésto nos define como empresa y nos motiva a replicarlo en todo el mundo aportando valor a cada contexto de manera innovadora, auténtica y confiable. La manera en que lo hacemos es a través de nuestro diferencial exclusivo como desarrolladora: líneas edilicias enfocadas en un abanico de experiencias —tanto de inversión como para vivir—. Y es en función de esto que organizamos nuestros procesos internos: no solo con la vanguardia en tecnología y certificaciones (BIM) sino poniendo en el centro al factor humano. En cada área AGUICONS® se potencia con profesionales expertos que aportan un caudal de conocimientos y nuevas ideas para contribuir al crecimiento y mejora de la sociedad. Porque eso es lo que nos guía: ser partícipes en construir el mañana.';
    $servicios = [
        'Ofrecemos soluciones integrales de construcción para el sector público y privado, ejecutando cada proyecto con solvencia técnica, eficiencia y cumplimiento riguroso de plazos. Nuestra trayectoria abarca obras de diversa escala y complejidad técnica.',
        'Principales Proyectos y Tipologías: Contamos con experiencia en arquitectura comercial y bancaria, incluyendo sucursales financieras como Banco Macro, oficinas corporativas y locales comerciales; desarrollo residencial, con viviendas unifamiliares de categoría y edificios multifamiliares de propiedad horizontal; infraestructura pública y de salud, como centros hospitalarios, establecimientos educativos y arquitectura institucional; y urbanización y espacios públicos, con la construcción de barrios de viviendas, infraestructura urbana y plazas comunitarias.',
        'Garantizamos una dirección de obra rigurosa, gestión eficiente de recursos y estándares superiores de calidad constructiva de principio a fin.',
    ];
    $suelo = [
        'Toda gran obra comienza por su vínculo con el terreno. El movimiento de suelos es la etapa en la que preparamos y transformamos el suelo natural para adaptarlo a las exigencias de cada proyecto, definiendo los niveles y condiciones necesarios para recibir sus fundaciones y estructuras.',
        'Contamos con maquinaria y equipamiento especializado de distintas capacidades, seleccionados según las características y requerimientos de cada intervención. Este equipamiento nos permite abordar trabajos de excavación, apertura y preparación de calles, demoliciones, desmontes previos al movimiento de suelos y adecuación de terrenos para proyectos de diversa escala, desde viviendas hasta desarrollos edilicios de mayor envergadura.',
        'Una primera intervención que, aunque permanece bajo la superficie, resulta fundamental para dar solidez a todo aquello que vendrá después.',
    ];
    $privacy = [
        ['Responsable', 'Aguicons, con domicilio en Av. Antártida Argentina 876, Posadas, Misiones, Argentina (info@aguicons.com), es responsable del tratamiento de los datos personales recabados en este sitio.'],
        ['Datos que recolectamos', 'Los que nos brindás al completar formularios (nombre, email, teléfono, mensaje y proyecto de interés) y, si aceptás las cookies, datos anónimos de navegación mediante Google Analytics.'],
        ['Finalidad', 'Responder tus consultas, enviarte la información o el brochure solicitado y contactarte por la propiedad de tu interés. No vendemos ni cedemos tus datos a terceros ajenos a estos fines.'],
        ['Cookies y analítica', 'Usamos cookies de analítica solo con tu consentimiento, para entender cómo se usa el sitio y mejorarlo. Podés rechazarlas desde el aviso que aparece al ingresar.'],
        ['Tus derechos', 'Conforme a la Ley 25.326 de Protección de Datos Personales, podés acceder, rectificar y suprimir tus datos escribiendo a info@aguicons.com. La Agencia de Acceso a la Información Pública, en su carácter de órgano de control, tiene la atribución de atender denuncias y reclamos por incumplimientos a las normas de protección de datos personales.'],
        ['Conservación', 'Conservamos tus datos mientras sea necesario para atender tu consulta y por los plazos que exija la ley.'],
        ['Cambios', 'Podemos actualizar esta política; la versión vigente es la publicada en esta página.'],
    ];

    $home = agui_upsert_page('inicio', 'Inicio', '', [], 'Aguicons, desarrolladora de Posadas, Misiones. Líneas edilicias de inversión y para vivir.');

    $c = agui_b_heading('NOSOTROS') . agui_b_para($nosotros) . agui_b_para('AGUICONS® construimos tu futuro.')
        . agui_b_heading('EQUIPO', 2, true)
        . agui_b_team([
            ['COMERCIAL', 'Equipo que conecta nuestra oferta con las personas, construyendo una experiencia de venta exclusiva con nuestros clientes.'],
            ['TÉCNICA', 'Profesionales que aportan su conocimiento y experiencia para planificar, coordinar y hacer realidad cada proyecto.'],
            ['ADMINISTRATIVA', 'Un grupo humano que aporta organización y control, gestionando los recursos económicos y financieros que sostienen el crecimiento de nuestra empresa.'],
        ])
        . agui_b_heading('MÁS SERVICIOS', 3, true)
        . agui_b_buttons([['Servicios de construcciones', home_url('/servicios-de-construccion/')], ['Movimiento de suelo', home_url('/movimientos-de-suelo/')]]);
    agui_upsert_page('nosotros', 'Nosotros', $c, ['_agui_bg' => 'gold'], 'Más de 15 años cambiando el skyline de las ciudades. Conocé al equipo de Aguicons.');

    $c = agui_b_heading('SERVICIOS DE CONSTRUCCIÓN');
    foreach ($servicios as $p) {
        $c .= agui_b_para($p);
    }
    $c .= agui_b_shortcode('[aguicons_consulta proyecto="Servicios de construcción"]');
    $p_serv = agui_upsert_page('servicios-de-construccion', 'Servicios de construcción', $c, [], 'Soluciones integrales de construcción para el sector público y privado.');

    $c = agui_b_heading('MOVIMIENTO DE SUELO');
    foreach ($suelo as $p) {
        $c .= agui_b_para($p);
    }
    $c .= agui_b_shortcode('[aguicons_consulta proyecto="Movimiento de suelo"]');
    $p_suelo = agui_upsert_page('movimientos-de-suelo', 'Movimiento de suelo', $c, [], 'Excavación, apertura de calles, demoliciones y adecuación de terrenos.');

    $c = agui_b_heading('ALQUILER · Unidades y Oficinas') . agui_b_para(AGUI_LOREM)
        . agui_b_shortcode('[aguicons_alquiler]') . agui_b_shortcode('[aguicons_consulta proyecto="Alquiler"]');
    $p_alq = agui_upsert_page('alquiler', 'Alquiler', $c, [], 'Unidades, oficinas y locales comerciales en alquiler en las líneas de Aguicons.');

    $c = agui_b_heading('CONVERSÁ CON NOSOTROS', 2, true) . agui_b_para('Dejanos tu consulta y te responderemos a la brevedad.', true)
        . agui_b_shortcode('[aguicons_contacto_form]') . agui_b_shortcode('[aguicons_mapa]');
    $p_cont = agui_upsert_page('contacto', 'Contacto', $c, [], 'Contactá a Aguicons: info@aguicons.com, Av. Antártida Argentina 876, Posadas.');

    $c = agui_b_heading('POLÍTICA DE PRIVACIDAD');
    foreach ($privacy as [$h, $t]) {
        $c .= agui_b_heading($h, 3) . agui_b_para($t);
    }
    $p_priv = agui_upsert_page('politica-de-privacidad', 'Política de privacidad', $c, [], 'Cómo tratamos los datos personales recabados en este sitio.');

    // Novedades (blog) y borrador de la página "Invertir"
    $p_nov = agui_upsert_page('novedades', 'Novedades', '', [], 'Lanzamientos, avances de obra y noticias de Aguicons.');
    update_option('page_for_posts', $p_nov);
    if (!get_page_by_path('invertir')) {
        $inv = agui_upsert_page('invertir', 'Invertir', agui_b_heading('INVERTÍ CON AGUICONS') . agui_b_para('Completar con la modalidad de compra, la financiación y los beneficios de invertir en cada línea.') . agui_b_shortcode('[aguicons_faq]') . agui_b_shortcode('[aguicons_consulta proyecto="Inversiones"]'), [], 'Cómo invertir con Aguicons.');
        wp_update_post(['ID' => $inv, 'post_status' => 'draft']);
    }

    // Líneas edilicias
    $baradiseText = "Fiel a la tradición de Aguicons® de bautizar con un nombre propio y exclusivo al proyecto insignia de cada nueva línea. Baradise® nace para inaugurar nuestra propuesta residencial más elevada hasta el momento. La expresión máxima de lujo, arquitectura orgánica y diseño contemporáneo frente al río.\n\nBARADISE® propone una forma sutil y renovada de habitar la ciudad a través de una arquitectura que combina líneas contemporáneas y materiales nobles. Concebido para integrarse de manera fluida con su entorno, el edificio aprovecha grandes ventanales y distribuciones abiertas que invitan a la luz natural y abren vistas panorámicas hacia el río, creando espacios que transmiten libertad, calma y confort.\n\nCon exclusivas residencias de 2 y 3 dormitorios en suite proyectadas al detalle.";

    $acros = $nums('BENROW/BENROW ACROS', ['11', '12', '16', '19', '20']);
    $vantier = array_merge($nums('TIER/PROTAGÓNICO/EXTERIOR', ['11', '14', '17', '6', '1']), $nums('TIER/PROTAGÓNICO/INTERIOR', ['5', '6', '23']), $nums('TIER/VANTIER', ['9', '5', '15', '16', '20', '8', '3']));
    $loftier = ["$L/TIER/LOFTIER/Copia de 01.png", "$L/TIER/LOFTIER/Copia de 02.png", "$L/TIER/LOFTIER/Copia de Copia de @mathycorrea - 26.JPG"];
    $alarif = ['HOME/ALARIF/Copia de 1.png', 'HOME/ALARIF/Copia de 2.png'];

    agui_upsert_line(['slug' => 'baradise', 'logo' => 'LOGOS LINEAS/baradise.png', 'title' => 'Baradise', 'order' => 1, 'script' => true, 'text' => $baradiseText,
        'amenities' => ['Piscina', 'Solarium', 'Gimnasio', 'Sum', 'Co-working', 'Terraza Verde'], 'thumb' => 'HOME/BARADISE/Copia de 03.png',
        'gallery' => array_merge($nums('BARADISE/EXTERIOR', ['08', '10', '15', '18', '20']), $nums('BARADISE/HALL DE INGRESO', ['05', '08']), $nums('BARADISE/INTERIOR', ['01', '07', '08', '10']))]);

    agui_upsert_line(['slug' => 'velerian', 'logo' => 'LOGOS LINEAS/velerian.png', 'title' => 'Velerian', 'order' => 2, 'thumb' => 'HOME/VELERIAN/Copia de 06.png',
        'gallery' => ["$L/VELERIAN/EXTERIOR/Copia de 06.png", "$L/VELERIAN/EXTERIOR/Copia de 08.png", "$L/VELERIAN/EXTERIOR/Copia de Copia de IMG_2360.JPG", 'HOME/VELERIAN/Copia de 05.png', 'HOME/VELERIAN/Copia de 07.png']]);

    $benrow = agui_upsert_line(['slug' => 'benrow', 'logo' => 'LOGOS LINEAS/benrow.png', 'title' => 'Benrow', 'order' => 3, 'thumb' => 'HOME/BENROW/Copia de 19.png', 'cards_title' => 'PROTAGÓNICO (ACROS)']);
    agui_upsert_line(['slug' => 'benrow-acros', 'title' => 'Benrow Acros', 'order' => 1, 'parent' => $benrow, 'thumb' => $acros[2], 'gallery' => $acros]);
    agui_upsert_line(['slug' => 'benrow-local-comercial', 'title' => 'Benrow', 'order' => 2, 'parent' => $benrow, 'thumb' => 'HOME/BENROW/Copia de 18.png',
        'badge_title' => 'ALQUILER', 'badge_text' => 'Local Comercial', 'gallery' => ['HOME/BENROW/Copia de 18.png', 'HOME/BENROW/Copia de 19.png']]);

    $tier = agui_upsert_line(['slug' => 'tier', 'logo' => 'LOGOS LINEAS/tier.png', 'title' => 'Tier', 'order' => 4, 'thumb' => 'HOME/TIER/Copia de 17.png', 'cards_title' => 'PROTAGÓNICO (VANTIER)']);
    agui_upsert_line(['slug' => 'vantier', 'title' => 'Vantier', 'order' => 1, 'parent' => $tier, 'thumb' => $vantier[3], 'gallery' => $vantier]);
    agui_upsert_line(['slug' => 'loftier', 'title' => 'Loftier', 'order' => 2, 'parent' => $tier, 'thumb' => $loftier[0], 'gallery' => $loftier]);

    $alar = agui_upsert_line(['slug' => 'alarif', 'logo' => 'LOGOS LINEAS/alarif.png', 'title' => 'Alarif', 'order' => 5, 'thumb' => 'HOME/ALARIF/Copia de 1.png', 'cards_title' => 'PROTAGÓNICO (RIVÁ)', 'gallery' => $alarif]);
    agui_upsert_line(['slug' => 'alarif-fazara', 'title' => 'Alarif Fazara', 'order' => 1, 'parent' => $alar, 'thumb' => 'HOME/ALARIF/Copia de 1.png',
        'badge_title' => 'ALQUILER', 'badge_text' => 'Unidades', 'gallery' => $alarif]);
    agui_upsert_line(['slug' => 'alarif-trench', 'title' => 'Alarif Trench', 'order' => 2, 'parent' => $alar, 'thumb' => 'HOME/ALARIF/Copia de 2.png',
        'badge_title' => 'ALQUILER', 'badge_text' => 'Unidades', 'gallery' => $alarif]);

    // Inicio como portada del sitio y enlaces permanentes limpios
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home);
    update_option('permalink_structure', '/%postname%/');

    // Menús
    $primary = wp_get_nav_menu_object('Principal') ?: null;
    $menu_id = $primary ? $primary->term_id : wp_create_nav_menu('Principal');
    foreach ((array) wp_get_nav_menu_items($menu_id) as $it) {
        wp_delete_post($it->ID, true);
    }
    $items = [['Inicio', $home], ['Nosotros', get_page_by_path('nosotros')->ID], ['Servicios', $p_serv], ['Movimiento de suelo', $p_suelo], ['Alquiler', $p_alq], ['Novedades', $p_nov], ['Contacto', $p_cont]];
    foreach ($items as $i => [$label, $pid]) {
        wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $label, 'menu-item-object' => 'page', 'menu-item-object-id' => $pid, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $i + 1]);
    }
    $footer = wp_get_nav_menu_object('Pie') ?: null;
    $fid = $footer ? $footer->term_id : wp_create_nav_menu('Pie');
    foreach ((array) wp_get_nav_menu_items($fid) as $it) {
        wp_delete_post($it->ID, true);
    }
    wp_update_nav_menu_item($fid, 0, ['menu-item-title' => 'Política de privacidad', 'menu-item-object' => 'page', 'menu-item-object-id' => $p_priv, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish']);
    set_theme_mod('nav_menu_locations', ['primary' => $menu_id, 'footer' => $fid]);

    update_option('agui_imported', 1);
    flush_rewrite_rules();
}

/** Para pruebas por WP-CLI: wp eval 'agui_run_import();' */
function agui_run_import(): void
{
    foreach (array_keys(agui_manifest()) as $rel) {
        agui_import_image($rel);
    }
    agui_import_content();
}
