<?php
/**
 * Panel de gestión de Aguicons: menú propio, panel de inicio, líneas (duplicar / pausar), consultas y ayuda.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_theme_file_path('inc/wizard.php');
require_once get_theme_file_path('inc/structure.php');

const AGUI_PANEL = 'aguicons-panel';

/* ---------------------------------------------------------------------------------------------
 * Menú
 * ------------------------------------------------------------------------------------------- */

add_action('admin_menu', function () {
    $new = agui_new_leads_count();
    $badge = $new ? ' <span class="awaiting-mod">' . $new . '</span>' : '';
    add_menu_page('Aguicons', 'Aguicons' . $badge, 'manage_options', AGUI_PANEL, 'agui_dashboard_page', 'dashicons-building', 3);
    add_submenu_page(AGUI_PANEL, 'Panel', 'Panel', 'manage_options', AGUI_PANEL, 'agui_dashboard_page');
}, 5);

add_action('admin_menu', function () {
    add_submenu_page(AGUI_PANEL, 'Nueva línea', '➕ Nueva línea (asistente)', 'manage_options', 'aguicons-wizard', 'agui_wizard_page');
    add_submenu_page(AGUI_PANEL, 'Estructura del sitio', 'Estructura del sitio', 'manage_options', 'aguicons-structure', 'agui_structure_page');
    add_submenu_page(AGUI_PANEL, 'Consultas', 'Consultas' . (agui_new_leads_count() ? ' <span class="awaiting-mod">' . agui_new_leads_count() . '</span>' : ''), 'manage_options', 'aguicons-leads', 'agui_leads_page');
    add_submenu_page(AGUI_PANEL, 'Contenido', 'Contenido', 'manage_options', 'aguicons-content', 'agui_content_page');
    add_submenu_page(AGUI_PANEL, 'Ayuda', 'Ayuda', 'manage_options', 'aguicons-help', 'agui_help_page');
}, 20);

/** Ajustes pasa a vivir dentro del menú Aguicons. */
add_action('admin_menu', function () {
    remove_submenu_page('options-general.php', 'aguicons');
    add_submenu_page(AGUI_PANEL, 'Ajustes', 'Ajustes', 'manage_options', 'aguicons', 'agui_render_options_page');
}, 21);

function agui_is_panel_screen(): bool
{
    $s = get_current_screen();
    return $s && (strpos((string) $s->id, 'aguicons') !== false || in_array($s->post_type, ['linea', 'testimonio', 'faq', 'consulta'], true));
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('aguicons-panel', get_theme_file_uri('assets/css/panel.css'), [], AGUI_VERSION . '.' . filemtime(get_theme_file_path('assets/css/panel.css')));
    wp_enqueue_script('aguicons-panel', get_theme_file_uri('assets/js/panel.js'), ['jquery', 'jquery-ui-sortable', 'aguicons-admin'], AGUI_VERSION . '.' . filemtime(get_theme_file_path('assets/js/panel.js')), true);
    wp_localize_script('aguicons-panel', 'AGUI_PANEL', [
        'ajax' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('agui_wizard'),
    ]);
});

/* ---------------------------------------------------------------------------------------------
 * Estado de cada línea ("qué falta")
 * ------------------------------------------------------------------------------------------- */

/** @return array{0:int,1:string[]} porcentaje completo y lista de pendientes */
function agui_line_check(WP_Post $p): array
{
    $m = fn($k) => (string) get_post_meta($p->ID, '_agui_' . $k, true);
    $checks = [
        'Foto de la tarjeta' => has_post_thumbnail($p),
        'Texto propio (hoy es de relleno)' => trim(wp_strip_all_tags($p->post_content)) !== '' && stripos($p->post_content, 'lorem ipsum') === false,
        'Logo de la línea' => (int) $m('logo_id') > 0,
        'Galería de fotos' => count(array_filter(explode(',', $m('gallery')))) >= 3,
        'Estado del proyecto' => $m('status') !== '',
        'Ficha técnica completa' => $m('facts') !== '' && stripos($m('facts'), 'a confirmar') === false,
        'Ubicación (mapa)' => $m('address') !== '' || $m('coords') !== '',
    ];
    $missing = array_keys(array_filter($checks, fn($ok) => !$ok));
    $score = (int) round((count($checks) - count($missing)) / count($checks) * 100);
    return [$score, $missing];
}

function agui_new_leads_count(): int
{
    static $n = null;
    if ($n === null) {
        $q = new WP_Query(['post_type' => 'consulta', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false,
            'meta_query' => [['relation' => 'OR', ['key' => '_agui_estado', 'compare' => 'NOT EXISTS'], ['key' => '_agui_estado', 'value' => 'nueva']]]]);
        $n = (int) $q->found_posts;
    }
    return $n;
}

/* ---------------------------------------------------------------------------------------------
 * Panel de inicio
 * ------------------------------------------------------------------------------------------- */

function agui_dashboard_page(): void
{
    $lines = get_posts(['post_type' => 'linea', 'post_status' => ['publish', 'draft'], 'numberposts' => -1, 'orderby' => ['post_parent' => 'ASC', 'menu_order' => 'ASC']]);
    $pub = count(array_filter($lines, fn($l) => $l->post_status === 'publish'));
    $leads_total = (int) wp_count_posts('consulta')->publish;
    ?>
    <div class="wrap agui-panel">
        <h1>Panel de Aguicons</h1>
        <?php if (!get_option('agui_imported')) : ?>
            <div class="notice notice-warning"><p>Falta cargar el contenido inicial (fotos, páginas y líneas). <a class="button button-primary" href="<?php echo esc_url(admin_url('tools.php?page=aguicons-import')); ?>">Cargar contenido inicial</a></p></div>
        <?php endif; ?>

        <div class="agui-cards">
            <div class="agui-stat"><strong><?php echo (int) $pub; ?></strong><span>Líneas publicadas</span></div>
            <div class="agui-stat"><strong><?php echo count($lines) - $pub; ?></strong><span>En borrador / pausadas</span></div>
            <a class="agui-stat <?php echo agui_new_leads_count() ? 'is-alert' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=aguicons-leads')); ?>"><strong><?php echo agui_new_leads_count(); ?></strong><span>Consultas nuevas (<?php echo $leads_total; ?> en total)</span></a>
        </div>

        <h2>¿Qué querés hacer?</h2>
        <p class="agui-actions">
            <a class="button button-primary button-hero" href="<?php echo esc_url(admin_url('admin.php?page=aguicons-wizard')); ?>">➕ Cargar una línea nueva</a>
            <a class="button button-hero" href="<?php echo esc_url(admin_url('admin.php?page=aguicons-structure')); ?>">Editar la estructura del sitio</a>
            <a class="button button-hero" href="<?php echo esc_url(admin_url('admin.php?page=aguicons-leads')); ?>">Ver consultas</a>
            <a class="button button-hero" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Ver el sitio ↗</a>
        </p>

        <h2>Estado de cada línea</h2>
        <p class="description">El porcentaje indica cuántos datos clave están completos. Hacé clic en <strong>Completar</strong> para abrir el asistente en esa línea.</p>
        <table class="widefat striped agui-table">
            <thead><tr><th>Línea</th><th>Estado</th><th>Completo</th><th>Falta</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($lines as $p) :
                [$score, $missing] = agui_line_check($p);
                $indent = $p->post_parent ? '— ' : '';
                ?>
                <tr>
                    <td><strong><?php echo esc_html($indent . $p->post_title); ?></strong></td>
                    <td><?php echo $p->post_status === 'publish' ? '<span class="agui-pill ok">Publicada</span>' : '<span class="agui-pill off">Borrador</span>'; ?></td>
                    <td><div class="agui-bar"><span style="width:<?php echo (int) $score; ?>%"></span></div> <?php echo (int) $score; ?>%</td>
                    <td class="agui-missing"><?php echo $missing ? esc_html(implode(' · ', $missing)) : '✔ Todo listo'; ?></td>
                    <td>
                        <a class="button button-small button-primary" href="<?php echo esc_url(admin_url('admin.php?page=aguicons-wizard&line=' . $p->ID)); ?>">Completar</a>
                        <?php if ($p->post_status === 'publish') : ?><a class="button button-small" href="<?php echo esc_url(get_permalink($p)); ?>" target="_blank" rel="noopener">Ver</a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$lines) : ?><tr><td colspan="5">Todavía no hay líneas. Empezá con “Cargar una línea nueva”.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/* ---------------------------------------------------------------------------------------------
 * Líneas: columnas y acciones (Asistente, Duplicar, Pausar)
 * ------------------------------------------------------------------------------------------- */

add_action('init', function () {
    // Los tipos de contenido cuelgan del menú Aguicons.
    foreach (['linea', 'testimonio', 'faq'] as $t) {
        add_filter("register_{$t}_post_type_args", function ($args) {
            $args['show_in_menu'] = AGUI_PANEL;
            return $args;
        });
    }
    add_filter('register_consulta_post_type_args', function ($args) {
        $args['show_in_menu'] = false;
        return $args;
    });
}, 0);

add_filter('page_row_actions', function ($actions, $post) {
    if ($post->post_type !== 'linea') {
        return $actions;
    }
    $dup = wp_nonce_url(admin_url('admin-post.php?action=agui_duplicate&id=' . $post->ID), 'agui_dup_' . $post->ID);
    $tog = wp_nonce_url(admin_url('admin-post.php?action=agui_toggle&id=' . $post->ID), 'agui_tog_' . $post->ID);
    return array_merge([
        'agui_wizard' => '<a href="' . esc_url(admin_url('admin.php?page=aguicons-wizard&line=' . $post->ID)) . '"><strong>Asistente</strong></a>',
        'agui_dup' => '<a href="' . esc_url($dup) . '">Duplicar</a>',
        'agui_tog' => '<a href="' . esc_url($tog) . '">' . ($post->post_status === 'publish' ? 'Pausar' : 'Publicar') . '</a>',
    ], $actions);
}, 10, 2);

add_filter('manage_linea_posts_columns', function ($cols) {
    $cols['agui_score'] = 'Completo';
    return $cols;
});
add_action('manage_linea_posts_custom_column', function ($col, $id) {
    if ($col === 'agui_score') {
        [$score] = agui_line_check(get_post($id));
        echo '<div class="agui-bar"><span style="width:' . (int) $score . '%"></span></div> ' . (int) $score . '%';
    }
}, 10, 2);

add_action('admin_post_agui_duplicate', function () {
    $id = (int) ($_GET['id'] ?? 0);
    check_admin_referer('agui_dup_' . $id);
    if (!current_user_can('manage_options') || !($src = get_post($id))) {
        wp_die('Sin permiso');
    }
    $new = wp_insert_post([
        'post_type' => 'linea', 'post_status' => 'draft', 'post_title' => $src->post_title . ' (copia)',
        'post_content' => $src->post_content, 'post_excerpt' => $src->post_excerpt, 'post_parent' => $src->post_parent, 'menu_order' => $src->menu_order + 1,
    ]);
    foreach (get_post_meta($id) as $k => $v) {
        if (strpos($k, '_agui_') === 0 || $k === '_thumbnail_id') {
            update_post_meta($new, $k, maybe_unserialize($v[0]));
        }
    }
    wp_safe_redirect(admin_url('admin.php?page=aguicons-wizard&line=' . $new . '&copied=1'));
    exit;
});

add_action('admin_post_agui_toggle', function () {
    $id = (int) ($_GET['id'] ?? 0);
    check_admin_referer('agui_tog_' . $id);
    $p = get_post($id);
    if (!current_user_can('manage_options') || !$p) {
        wp_die('Sin permiso');
    }
    wp_update_post(['ID' => $id, 'post_status' => $p->post_status === 'publish' ? 'draft' : 'publish']);
    wp_safe_redirect(wp_get_referer() ?: admin_url('edit.php?post_type=linea'));
    exit;
});

/* ---------------------------------------------------------------------------------------------
 * Consultas con estado (Nueva / Atendida) y exportación
 * ------------------------------------------------------------------------------------------- */

function agui_leads_query(array $f): WP_Query
{
    $mq = [];
    if (!empty($f['estado'])) {
        $mq[] = $f['estado'] === 'nueva'
            ? ['relation' => 'OR', ['key' => '_agui_estado', 'compare' => 'NOT EXISTS'], ['key' => '_agui_estado', 'value' => 'nueva']]
            : ['key' => '_agui_estado', 'value' => 'atendida'];
    }
    if (!empty($f['proyecto'])) {
        $mq[] = ['key' => '_agui_proyecto', 'value' => $f['proyecto']];
    }
    if (!empty($f['tipo'])) {
        $mq[] = ['key' => '_agui_tipo', 'value' => $f['tipo']];
    }
    return new WP_Query(['post_type' => 'consulta', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => $mq ?: []]);
}

function agui_leads_page(): void
{
    $f = [
        'estado' => sanitize_key($_GET['estado'] ?? ''),
        'proyecto' => sanitize_text_field(wp_unslash($_GET['proyecto'] ?? '')),
        'tipo' => sanitize_text_field(wp_unslash($_GET['tipo'] ?? '')),
    ];
    $q = agui_leads_query($f);
    global $wpdb;
    $projects = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_agui_proyecto' AND meta_value <> '' ORDER BY meta_value");
    $export = wp_nonce_url(add_query_arg(array_merge(['action' => 'agui_export'], array_filter($f)), admin_url('admin-post.php')), 'agui_export');
    ?>
    <div class="wrap agui-panel">
        <h1>Consultas recibidas</h1>
        <form method="get" class="agui-filters">
            <input type="hidden" name="page" value="aguicons-leads">
            <select name="estado"><option value="">Todos los estados</option><option value="nueva" <?php selected($f['estado'], 'nueva'); ?>>Nuevas</option><option value="atendida" <?php selected($f['estado'], 'atendida'); ?>>Atendidas</option></select>
            <select name="proyecto"><option value="">Todos los proyectos</option><?php foreach ($projects as $p) : ?><option <?php selected($f['proyecto'], $p); ?>><?php echo esc_html($p); ?></option><?php endforeach; ?></select>
            <select name="tipo"><option value="">Todos los tipos</option><?php foreach (['Consulta por proyecto', 'Descarga de brochure', 'Contacto'] as $t) : ?><option <?php selected($f['tipo'], $t); ?>><?php echo esc_html($t); ?></option><?php endforeach; ?></select>
            <button class="button">Filtrar</button>
            <a class="button" href="<?php echo esc_url($export); ?>">⬇ Exportar a Excel (CSV)</a>
        </form>
        <table class="widefat striped agui-table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Proyecto</th><th>Nombre</th><th>Contacto</th><th>Mensaje</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($q->posts as $l) :
                $m = fn($k) => (string) get_post_meta($l->ID, '_agui_' . $k, true);
                $estado = $m('estado') ?: 'nueva';
                $tog = wp_nonce_url(admin_url('admin-post.php?action=agui_lead_state&id=' . $l->ID . '&to=' . ($estado === 'nueva' ? 'atendida' : 'nueva')), 'agui_lead_' . $l->ID);
                ?>
                <tr class="<?php echo $estado === 'nueva' ? 'agui-new' : ''; ?>">
                    <td><?php echo esc_html(get_the_date('d/m/Y H:i', $l)); ?></td>
                    <td><?php echo esc_html($m('tipo')); ?></td>
                    <td><?php echo esc_html($m('proyecto')); ?></td>
                    <td><strong><?php echo esc_html($m('nombre')); ?></strong></td>
                    <td><a href="mailto:<?php echo esc_attr($m('email')); ?>"><?php echo esc_html($m('email')); ?></a><br><?php echo esc_html($m('telefono')); ?></td>
                    <td><?php echo esc_html(wp_trim_words($m('mensaje'), 18)); ?></td>
                    <td><span class="agui-pill <?php echo $estado === 'nueva' ? 'alert' : 'ok'; ?>"><?php echo $estado === 'nueva' ? 'Nueva' : 'Atendida'; ?></span>
                        <a class="button button-small" href="<?php echo esc_url($tog); ?>"><?php echo $estado === 'nueva' ? 'Marcar atendida' : 'Reabrir'; ?></a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$q->posts) : ?><tr><td colspan="7">No hay consultas con estos filtros.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

add_action('admin_post_agui_lead_state', function () {
    $id = (int) ($_GET['id'] ?? 0);
    check_admin_referer('agui_lead_' . $id);
    if (!current_user_can('manage_options')) {
        wp_die('Sin permiso');
    }
    update_post_meta($id, '_agui_estado', ($_GET['to'] ?? '') === 'atendida' ? 'atendida' : 'nueva');
    wp_safe_redirect(wp_get_referer() ?: admin_url('admin.php?page=aguicons-leads'));
    exit;
});

add_action('admin_post_agui_export', function () {
    check_admin_referer('agui_export');
    if (!current_user_can('manage_options')) {
        wp_die('Sin permiso');
    }
    $q = agui_leads_query([
        'estado' => sanitize_key($_GET['estado'] ?? ''),
        'proyecto' => sanitize_text_field(wp_unslash($_GET['proyecto'] ?? '')),
        'tipo' => sanitize_text_field(wp_unslash($_GET['tipo'] ?? '')),
    ]);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=consultas-aguicons-' . gmdate('Ymd') . '.csv');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // para que Excel lea bien los acentos
    fputcsv($out, ['Fecha', 'Tipo', 'Proyecto', 'Nombre', 'Email', 'Teléfono', 'Mensaje', 'Estado', 'Página'], ';');
    foreach ($q->posts as $l) {
        $m = fn($k) => (string) get_post_meta($l->ID, '_agui_' . $k, true);
        fputcsv($out, [get_the_date('Y-m-d H:i', $l), $m('tipo'), $m('proyecto'), $m('nombre'), $m('email'), $m('telefono'), $m('mensaje'), $m('estado') ?: 'nueva', $m('pagina')], ';');
    }
    fclose($out);
    exit;
});

/* ---------------------------------------------------------------------------------------------
 * Contenido y ayuda
 * ------------------------------------------------------------------------------------------- */

function agui_content_page(): void
{
    $items = [
        ['Testimonios', 'Opiniones de clientes (solo reales y autorizadas).', admin_url('edit.php?post_type=testimonio'), admin_url('post-new.php?post_type=testimonio')],
        ['Preguntas frecuentes', 'Preguntas y respuestas para inversores y compradores.', admin_url('edit.php?post_type=faq'), admin_url('post-new.php?post_type=faq')],
        ['Novedades (blog)', 'Lanzamientos, avances de obra y noticias.', admin_url('edit.php'), admin_url('post-new.php')],
        ['Páginas', 'Nosotros, Servicios, Movimiento de suelo, Alquiler, Contacto, Privacidad.', admin_url('edit.php?post_type=page'), admin_url('post-new.php?post_type=page')],
        ['Carruseles de servicios', 'Fotos de Construcción y Movimiento de suelo.', admin_url('admin.php?page=aguicons-structure#carruseles-servicios'), admin_url('admin.php?page=aguicons-structure#carruseles-servicios')],
        ['Biblioteca de medios', 'Todas las fotos, logos y PDF.', admin_url('upload.php'), admin_url('media-new.php')],
    ];
    echo '<div class="wrap agui-panel"><h1>Contenido</h1><div class="agui-cards agui-cards-wide">';
    foreach ($items as [$t, $d, $list, $new]) {
        printf('<div class="agui-card"><h3>%s</h3><p>%s</p><a class="button" href="%s">Ver todo</a> <a class="button button-primary" href="%s">Agregar</a></div>', esc_html($t), esc_html($d), esc_url($list), esc_url($new));
    }
    echo '</div></div>';
}

function agui_help_page(): void
{
    ?>
    <div class="wrap agui-panel agui-help">
        <h1>Ayuda</h1>
        <h2>Cargar una línea nueva</h2>
        <ol>
            <li><strong>Aguicons → ➕ Nueva línea (asistente)</strong> y seguir los 5 pasos: datos, textos, imágenes, ficha y publicación.</li>
            <li>En cualquier paso podés apretar <em>Guardar borrador</em>; la línea no se ve en el sitio hasta que la <em>publiques</em>.</li>
            <li>Al final, el asistente muestra qué datos faltan y un botón para ver la página.</li>
        </ol>
        <h2>Cambiar algo de una línea que ya existe</h2>
        <p>En el <strong>Panel</strong> apretá <em>Completar</em> en esa línea, o en <strong>Líneas edilicias</strong> usá el enlace <em>Asistente</em>. Para ocultarla sin borrarla: <em>Pausar</em>. Para crear una parecida: <em>Duplicar</em>.</p>
        <h2>Estructura del sitio</h2>
        <p>Portada, estadísticas, orden del carrusel de líneas, menú y pie de página, todo en una sola pantalla.</p>
        <h2>Consultas</h2>
        <p>Cada formulario enviado llega por email a la casilla configurada en <em>Ajustes</em> y queda acá. Marcá <em>Atendida</em> cuando respondas y exportá a Excel cuando quieras.</p>
        <h2>Formatos útiles</h2>
        <ul>
            <li><strong>Ficha técnica:</strong> una línea por dato, <code>Dato: Valor</code>. Ej: <code>Superficie: 60 a 120 m²</code>.</li>
            <li><strong>Unidades:</strong> <code>Tipología | Superficie | Precio | Estado</code>. Ej: <code>2 dormitorios | 85 m² | USD 120.000 | Disponible</code>.</li>
            <li><strong>Coordenadas:</strong> en Google Maps, clic derecho sobre el lugar y copiar los dos números. Ej: <code>-27.3671, -55.8961</code>.</li>
            <li><strong>Video:</strong> enlace de YouTube, Vimeo, Matterport o un archivo .mp4.</li>
            <li><strong>Fotos:</strong> subir en buena calidad (hasta 2000 px de ancho); el sitio las optimiza solo.</li>
        </ul>
    </div>
    <?php
}
