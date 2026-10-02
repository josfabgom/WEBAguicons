<?php
/**
 * Extras de las líneas: unidades disponibles, avance de obra, video/360°, coordenadas para el mapa,
 * preguntas frecuentes y refuerzos de velocidad y seguridad.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ---------------------------------------------------------------------------------------------
 * Preguntas frecuentes (FAQ)
 * ------------------------------------------------------------------------------------------- */

add_action('init', function () {
    register_post_type('faq', [
        'labels' => ['name' => 'Preguntas frecuentes', 'singular_name' => 'Pregunta', 'add_new_item' => 'Agregar pregunta', 'edit_item' => 'Editar pregunta', 'menu_name' => 'Preguntas frecuentes'],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-editor-help',
        'menu_position' => 24,
        'supports' => ['title', 'editor', 'page-attributes'],
    ]);
});

/* ---------------------------------------------------------------------------------------------
 * Cuadro de edición en cada Línea
 * ------------------------------------------------------------------------------------------- */

add_action('add_meta_boxes', function () {
    add_meta_box('agui_linea_extra', 'Unidades, avance de obra, video y ubicación', 'agui_box_linea_extra', 'linea', 'normal', 'default');
});

function agui_box_linea_extra(WP_Post $post): void
{
    $m = fn($k) => (string) get_post_meta($post->ID, '_agui_' . $k, true);
    $avance = json_decode($m('avance'), true);
    $avance = is_array($avance) ? $avance : [];
    ?>
    <h4>Unidades disponibles</h4>
    <p class="description">Una unidad por línea, con el formato <code>Tipología | Superficie | Precio | Estado</code>. Se puede pegar desde Excel. Aparece una tabla con filtros.</p>
    <textarea name="agui[units]" rows="6" class="large-text code" placeholder="2 dormitorios | 85 m² | USD 120.000 | Disponible&#10;3 dormitorios | 120 m² | USD 180.000 | Reservada"><?php echo esc_textarea($m('units')); ?></textarea>

    <h4>Video o recorrido 360°</h4>
    <p><input type="url" name="agui[video]" class="large-text" value="<?php echo esc_attr($m('video')); ?>" placeholder="https://www.youtube.com/watch?v=… (también Vimeo, Matterport o un archivo .mp4)"></p>

    <h4>Ubicación para el mapa de proyectos</h4>
    <p><input type="text" name="agui[coords]" class="regular-text" value="<?php echo esc_attr($m('coords')); ?>" placeholder="-27.3671, -55.8961">
        <span class="description">Latitud, longitud. En Google Maps: clic derecho sobre el lugar y copiar los números que aparecen arriba.</span></p>

    <h4>Avance de obra</h4>
    <p class="description">Una entrada por fecha, con texto y fotos. Se muestra como línea de tiempo.</p>
    <div id="agui-avance" data-rows="<?php echo esc_attr(wp_json_encode($avance)); ?>"></div>
    <input type="hidden" name="agui[avance]" id="agui-avance-json" value="<?php echo esc_attr($m('avance')); ?>">
    <p><button type="button" class="button" id="agui-avance-add">Agregar entrada</button></p>
    <?php
}

add_action('save_post_linea', function ($post_id) {
    if (!isset($_POST['agui_nonce']) || !wp_verify_nonce($_POST['agui_nonce'], 'agui_save') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }
    $in = isset($_POST['agui']) && is_array($_POST['agui']) ? wp_unslash($_POST['agui']) : [];
    if (array_key_exists('units', $in)) {
        update_post_meta($post_id, '_agui_units', sanitize_textarea_field($in['units']));
    }
    if (array_key_exists('video', $in)) {
        update_post_meta($post_id, '_agui_video', esc_url_raw($in['video']));
    }
    if (array_key_exists('coords', $in)) {
        $c = preg_replace('/[^0-9.,\-\s]/', '', (string) $in['coords']);
        update_post_meta($post_id, '_agui_coords', trim($c));
    }
    if (array_key_exists('avance', $in)) {
        $rows = json_decode((string) $in['avance'], true);
        $clean = [];
        foreach (is_array($rows) ? $rows : [] as $r) {
            $date = sanitize_text_field($r['date'] ?? '');
            $text = sanitize_text_field($r['text'] ?? '');
            $ids = array_values(array_filter(array_map('intval', (array) ($r['ids'] ?? []))));
            if ($date !== '' || $text !== '' || $ids) {
                $clean[] = ['date' => $date, 'text' => $text, 'ids' => $ids];
            }
        }
        update_post_meta($post_id, '_agui_avance', wp_json_encode($clean, JSON_UNESCAPED_UNICODE));
    }
});

/* ---------------------------------------------------------------------------------------------
 * Piezas visuales
 * ------------------------------------------------------------------------------------------- */

function agui_units(WP_Post $post): string
{
    $rows = [];
    foreach (array_filter(array_map('trim', preg_split('/\R/', (string) get_post_meta($post->ID, '_agui_units', true)))) as $line) {
        $cells = array_map('trim', preg_split('/\s*[|\t]\s*/', $line));
        $rows[] = array_pad($cells, 4, '');
    }
    if (!$rows) {
        return '';
    }
    $types = array_values(array_unique(array_column($rows, 0)));
    $states = array_values(array_unique(array_filter(array_column($rows, 3))));
    sort($types);
    sort($states);
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container agui-narrow">
            <h2 class="agui-h2">UNIDADES DISPONIBLES</h2>
            <div class="agui-units" data-units>
                <div class="agui-units-filters">
                    <label>Tipología
                        <select data-filter="0"><option value="">Todas</option><?php foreach ($types as $t) : ?><option><?php echo esc_html($t); ?></option><?php endforeach; ?></select>
                    </label>
                    <?php if ($states) : ?>
                        <label>Estado
                            <select data-filter="3"><option value="">Todos</option><?php foreach ($states as $t) : ?><option><?php echo esc_html($t); ?></option><?php endforeach; ?></select>
                        </label>
                    <?php endif; ?>
                </div>
                <div class="agui-units-scroll">
                    <table>
                        <thead><tr><th>Tipología</th><th><button type="button" data-sort="1">Superficie ⇅</button></th><th>Precio</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach ($rows as $r) : ?>
                                <tr><td><?php echo esc_html($r[0]); ?></td><td data-num="<?php echo esc_attr((string) (float) str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $r[1]))); ?>"><?php echo esc_html($r[1]); ?></td><td><?php echo esc_html($r[2]); ?></td><td><?php echo esc_html($r[3]); ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="agui-small">Consultá disponibilidad y precios actualizados con nuestro equipo comercial.</p>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function agui_progress(WP_Post $post): string
{
    $rows = json_decode((string) get_post_meta($post->ID, '_agui_avance', true), true);
    if (!is_array($rows) || !$rows) {
        return '';
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container agui-narrow">
            <h2 class="agui-h2">AVANCE DE OBRA</h2>
            <ol class="agui-timeline">
                <?php foreach ($rows as $r) : ?>
                    <li>
                        <strong><?php echo esc_html($r['date'] ?? ''); ?></strong>
                        <?php if (!empty($r['text'])) : ?><p><?php echo esc_html($r['text']); ?></p><?php endif; ?>
                        <?php if (!empty($r['ids'])) : ?>
                            <div class="agui-gallery-track agui-thumbs">
                                <?php foreach ($r['ids'] as $id) : ?>
                                    <button type="button" class="agui-slide" data-full="<?php echo esc_url(wp_get_attachment_image_url((int) $id, 'full')); ?>" aria-label="Ampliar foto"><?php echo agui_img((int) $id, 'medium', ['draggable' => 'false']); ?></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

function agui_video(string $url, string $title = 'RECORRIDO Y VIDEO'): string
{
    if ($url === '') {
        return '';
    }
    if (preg_match('/\.(mp4|webm)(\?.*)?$/i', $url)) {
        $inner = '<video controls preload="metadata" playsinline src="' . esc_url($url) . '"></video>';
    } else {
        $embed = '';
        if (preg_match('#(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $mm)) {
            $embed = 'https://www.youtube-nocookie.com/embed/' . $mm[1];
        } elseif (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $mm)) {
            $embed = 'https://player.vimeo.com/video/' . $mm[1];
        }
        if ($embed !== '') {
            $inner = '<iframe src="' . esc_url($embed) . '" title="Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; xr-spatial-tracking" allowfullscreen></iframe>';
        } else {
            // Matterport, Kuula, etc.: se incrusta la dirección tal cual.
            $inner = '<iframe src="' . esc_url($url) . '" title="Recorrido virtual" allow="xr-spatial-tracking; fullscreen" allowfullscreen></iframe>';
        }
        $inner = preg_replace('/<iframe\b/', '<iframe loading="lazy"', (string) $inner, 1);
    }
    return '<section class="agui-section agui-reveal"><div class="agui-container agui-narrow">' . ($title !== '' ? '<h2 class="agui-h2">' . esc_html($title) . '</h2>' : '') . '<div class="agui-video">' . $inner . '</div></div></section>';
}

/** Mapa con un pin por línea (OpenStreetMap + Leaflet). Solo se muestra si alguna línea tiene coordenadas. */
function agui_projects_map(): string
{
    $points = [];
    foreach (agui_lines() as $p) {
        $c = (string) get_post_meta($p->ID, '_agui_coords', true);
        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $c, $mm)) {
            $points[] = ['name' => $p->post_title, 'url' => get_permalink($p), 'lat' => (float) $mm[1], 'lng' => (float) $mm[2]];
        }
    }
    if (!$points) {
        return '';
    }
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
    return '<section class="agui-section agui-reveal"><div class="agui-container"><h2 class="agui-h2">NUESTROS PROYECTOS EN EL MAPA</h2>'
        . '<div id="agui-map" class="agui-map" role="region" aria-label="Mapa de proyectos" data-points="' . esc_attr(wp_json_encode($points, JSON_UNESCAPED_UNICODE)) . '"></div></div></section>';
}

function agui_faq(string $title = 'PREGUNTAS FRECUENTES'): string
{
    $items = get_posts(['post_type' => 'faq', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'ASC']]);
    if (!$items) {
        return '';
    }
    $schema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container agui-narrow">
            <h2 class="agui-h2"><?php echo esc_html($title); ?></h2>
            <div class="agui-faq">
                <?php foreach ($items as $q) :
                    $a = trim(wp_strip_all_tags(apply_filters('the_content', $q->post_content)));
                    $schema['mainEntity'][] = ['@type' => 'Question', 'name' => $q->post_title, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
                    ?>
                    <details><summary><?php echo esc_html($q->post_title); ?></summary><div><?php echo wp_kses_post(apply_filters('the_content', $q->post_content)); ?></div></details>
                <?php endforeach; ?>
            </div>
            <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

add_shortcode('aguicons_faq', fn() => agui_faq());
add_shortcode('aguicons_mapa_proyectos', fn() => agui_projects_map());

/* ---------------------------------------------------------------------------------------------
 * Velocidad
 * ------------------------------------------------------------------------------------------- */

add_filter('wp_resource_hints', function ($urls, $relation) {
    if ($relation === 'preconnect') {
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin'];
        $urls[] = 'https://fonts.googleapis.com';
    }
    return $urls;
}, 10, 2);

add_action('init', function () {
    // Sin emojis ni enlaces innecesarios en el <head>.
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
});

add_filter('wp_lazy_loading_enabled', '__return_true');

/* ---------------------------------------------------------------------------------------------
 * Seguridad básica
 * ------------------------------------------------------------------------------------------- */

add_filter('xmlrpc_enabled', '__return_false');

// No exponer la lista de usuarios a visitantes (por REST ni por ?author=N).
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});
add_action('template_redirect', function () {
    if (is_author() && !is_user_logged_in()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});
add_filter('login_errors', fn() => 'Los datos de acceso no son correctos.');

/* ---------------------------------------------------------------------------------------------
 * Novedades (blog)
 * ------------------------------------------------------------------------------------------- */

add_filter('excerpt_length', fn() => 28);
add_filter('excerpt_more', fn() => '…');
