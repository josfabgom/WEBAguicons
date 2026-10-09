<?php
/**
 * Tipos de contenido: Líneas edilicias, Testimonios y Consultas recibidas.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_post_type('linea', [
        'labels' => [
            'name' => 'Líneas edilicias',
            'singular_name' => 'Línea edilicia',
            'add_new' => 'Agregar línea',
            'add_new_item' => 'Agregar línea o proyecto',
            'edit_item' => 'Editar línea',
            'all_items' => 'Todas las líneas',
            'menu_name' => 'Líneas edilicias',
        ],
        'public' => true,
        'hierarchical' => true,
        'menu_icon' => 'dashicons-building',
        'menu_position' => 21,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes', 'excerpt'],
        'rewrite' => ['slug' => 'lineas', 'with_front' => false],
        'show_in_rest' => true,
        'has_archive' => false,
    ]);

    register_post_type('testimonio', [
        'labels' => [
            'name' => 'Testimonios',
            'singular_name' => 'Testimonio',
            'add_new_item' => 'Agregar testimonio',
            'edit_item' => 'Editar testimonio',
            'menu_name' => 'Testimonios',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-format-quote',
        'menu_position' => 22,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('consulta', [
        'labels' => [
            'name' => 'Consultas recibidas',
            'singular_name' => 'Consulta',
            'menu_name' => 'Consultas',
            'edit_item' => 'Ver consulta',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-email-alt',
        'menu_position' => 23,
        'supports' => ['title'],
        'capability_type' => 'post',
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true,
    ]);
});

/* ---------------------------------------------------------------------------------------------
 * Campos de las Líneas
 * ------------------------------------------------------------------------------------------- */

const AGUI_STATUSES = ['' => '— Sin estado —', 'En pozo' => 'En pozo', 'En construcción' => 'En construcción', 'Entregado' => 'Entregado', 'Últimas unidades' => 'Últimas unidades'];

add_action('add_meta_boxes', function () {
    add_meta_box('agui_linea_main', 'Datos de la línea / proyecto', 'agui_box_linea_main', 'linea', 'normal', 'high');
    add_meta_box('agui_linea_gallery', 'Galería de imágenes (carrusel)', 'agui_box_linea_gallery', 'linea', 'normal', 'default');
    add_meta_box('agui_linea_facts', 'Ficha técnica, estado y mapa', 'agui_box_linea_facts', 'linea', 'normal', 'default');
    add_meta_box('agui_linea_side', 'Carrusel de inicio y cartel', 'agui_box_linea_side', 'linea', 'side', 'default');
    add_meta_box('agui_testimonio', 'Datos del testimonio', 'agui_box_testimonio', 'testimonio', 'side', 'default');
    add_meta_box('agui_page_bg', 'Fondo de la página', 'agui_box_page_bg', 'page', 'side', 'default');
    add_meta_box('agui_page_gallery', 'Carrusel de imágenes de la página (opcional)', 'agui_box_linea_gallery', 'page', 'normal', 'default');
    add_meta_box('agui_consulta', 'Datos de la consulta', 'agui_box_consulta', 'consulta', 'normal', 'high');
});

function agui_field_image(string $name, int $id, string $label, string $help = ''): void
{
    ?>
    <div class="agui-media-field" data-multiple="0">
        <strong><?php echo esc_html($label); ?></strong>
        <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr((string) $id); ?>">
        <div class="agui-media-preview"><?php echo $id ? wp_get_attachment_image($id, 'medium') : ''; ?></div>
        <button type="button" class="button agui-media-pick">Elegir</button>
        <button type="button" class="button-link agui-media-clear">Quitar</button>
        <?php if ($help) : ?><p class="description"><?php echo esc_html($help); ?></p><?php endif; ?>
    </div>
    <?php
}

function agui_box_linea_main(WP_Post $post): void
{
    wp_nonce_field('agui_save', 'agui_nonce');
    $m = fn($k, $d = '') => get_post_meta($post->ID, '_agui_' . $k, true) ?: $d;
    ?>
    <p class="description">El <strong>título</strong> y el <strong>texto principal</strong> (editor de arriba) forman la cabecera negra de la página. La <strong>imagen destacada</strong> (a la derecha) es la foto de la tarjeta.</p>
    <p>
        <label><input type="checkbox" name="agui[script_title]" value="1" <?php checked($m('script_title'), '1'); ?>>
            Mostrar el nombre con tipografía decorativa (estilo Baradise)</label>
    </p>
    <?php agui_field_image('agui[logo_id]', (int) $m('logo_id', 0), 'Logo de la línea (opcional)', 'Si se carga, reemplaza al nombre escrito en la cabecera negra. Conviene un PNG con fondo transparente y letras blancas.'); ?>
    <p>
        <label for="agui_amenities"><strong>Amenities</strong> (uno por línea)</label><br>
        <textarea id="agui_amenities" name="agui[amenities]" rows="4" class="large-text" placeholder="Piscina&#10;Solarium&#10;Gimnasio"><?php echo esc_textarea($m('amenities')); ?></textarea>
    </p>
    <p>
        <label for="agui_cards_title"><strong>Título de las tarjetas de sub-proyectos</strong></label><br>
        <input type="text" id="agui_cards_title" name="agui[cards_title]" class="regular-text" value="<?php echo esc_attr($m('cards_title')); ?>" placeholder="PROTAGÓNICO (ACROS)">
        <span class="description">Solo si esta línea tiene sub-proyectos (se cargan como líneas hijas con “Superior” en Atributos).</span>
    </p>
    <?php
}

function agui_box_linea_gallery(WP_Post $post): void
{
    $ids = array_filter(array_map('intval', explode(',', (string) get_post_meta($post->ID, '_agui_gallery', true))));
    ?>
    <div class="agui-media-field agui-gallery" data-multiple="1">
        <input type="hidden" name="agui[gallery]" value="<?php echo esc_attr(implode(',', $ids)); ?>">
        <ul class="agui-gallery-list">
            <?php foreach ($ids as $id) : ?>
                <li data-id="<?php echo (int) $id; ?>"><?php echo wp_get_attachment_image($id, 'thumbnail'); ?><button type="button" class="agui-gallery-remove" aria-label="Quitar">×</button></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="button agui-media-pick">Agregar imágenes</button>
        <p class="description">Arrastre las miniaturas para cambiar el orden. Estas fotos forman el “Carrusel de vistas”.</p>
    </div>
    <?php
}

function agui_box_linea_facts(WP_Post $post): void
{
    $m = fn($k) => get_post_meta($post->ID, '_agui_' . $k, true);
    $status = $m('status');
    ?>
    <p>
        <label for="agui_status"><strong>Estado del proyecto</strong></label><br>
        <select id="agui_status" name="agui[status]">
            <?php foreach (AGUI_STATUSES as $v => $l) : ?>
                <option value="<?php echo esc_attr($v); ?>" <?php selected($status, $v); ?>><?php echo esc_html($l); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="agui_facts"><strong>Datos de la ficha técnica</strong> (uno por línea, con el formato <code>Dato: Valor</code>)</label><br>
        <textarea id="agui_facts" name="agui[facts]" rows="5" class="large-text" placeholder="Ubicación: Posadas, Misiones&#10;Superficie: 60 a 120 m²&#10;Unidades: 24&#10;Entrega: 2027"><?php echo esc_textarea($m('facts')); ?></textarea>
    </p>
    <p>
        <label for="agui_address"><strong>Dirección o lugar para el mapa</strong></label><br>
        <input type="text" id="agui_address" name="agui[address]" class="large-text" value="<?php echo esc_attr($m('address')); ?>" placeholder="Costanera, Posadas, Misiones, Argentina">
        <span class="description">Si se completa, se muestra el mapa de Google.</span>
    </p>
    <?php agui_field_file('agui[brochure_id]', (int) $m('brochure_id'), 'Brochure en PDF', 'Si no se carga, al pedir el brochure se avisa que llegará por email.'); ?>
    <?php
}

function agui_field_file(string $name, int $id, string $label, string $help = ''): void
{
    ?>
    <div class="agui-file-field">
        <strong><?php echo esc_html($label); ?></strong><br>
        <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr((string) $id); ?>">
        <span class="agui-file-name"><?php echo $id ? esc_html(basename((string) get_attached_file($id))) : 'Ningún archivo'; ?></span>
        <button type="button" class="button agui-file-pick">Elegir PDF</button>
        <button type="button" class="button-link agui-file-clear">Quitar</button>
        <?php if ($help) : ?><p class="description"><?php echo esc_html($help); ?></p><?php endif; ?>
    </div>
    <?php
}

function agui_box_linea_side(WP_Post $post): void
{
    $m = fn($k) => get_post_meta($post->ID, '_agui_' . $k, true);
    $show = get_post_meta($post->ID, '_agui_show_home', true);
    $show = $show === '' ? ($post->post_parent ? '0' : '1') : $show;
    ?>
    <p><label><input type="checkbox" name="agui[show_home]" value="1" <?php checked($show, '1'); ?>> Mostrar en el carrusel del inicio</label></p>
    <p class="description">La foto es la <em>imagen destacada</em>. El orden se define con “Orden” en Atributos.</p>
    <hr>
    <p><strong>Cartel dorado</strong> (ej: ALQUILER – Unidades)</p>
    <p><input type="text" name="agui[badge_title]" class="widefat" value="<?php echo esc_attr($m('badge_title')); ?>" placeholder="ALQUILER"></p>
    <p><input type="text" name="agui[badge_text]" class="widefat" value="<?php echo esc_attr($m('badge_text')); ?>" placeholder="Unidades"></p>
    <p><input type="text" name="agui[rental_name]" class="widefat" value="<?php echo esc_attr($m('rental_name')); ?>" placeholder="Nombre en la página Alquiler (si es distinto)"></p>
    <p class="description">Las líneas con cartel aparecen en la página de Alquiler.</p>
    <?php
}

function agui_box_testimonio(WP_Post $post): void
{
    wp_nonce_field('agui_save', 'agui_nonce');
    ?>
    <p><label for="agui_role"><strong>Rol</strong> (ej: Propietario de Baradise)</label><br>
        <input type="text" id="agui_role" name="agui[role]" class="widefat" value="<?php echo esc_attr((string) get_post_meta($post->ID, '_agui_role', true)); ?>"></p>
    <p class="description">El título es el nombre. El texto del testimonio va en el editor. La foto es la imagen destacada (opcional). Cargue solo opiniones reales y autorizadas.</p>
    <?php
}

function agui_box_page_bg(WP_Post $post): void
{
    wp_nonce_field('agui_save', 'agui_nonce');
    $bg = get_post_meta($post->ID, '_agui_bg', true);
    ?>
    <select name="agui[bg]">
        <option value="">Blanco</option>
        <option value="gold" <?php selected($bg, 'gold'); ?>>Dorado</option>
    </select>
    <?php
}

function agui_box_consulta(WP_Post $post): void
{
    $fields = ['tipo' => 'Tipo', 'proyecto' => 'Proyecto', 'nombre' => 'Nombre', 'email' => 'Email', 'telefono' => 'Teléfono', 'mensaje' => 'Mensaje', 'pagina' => 'Página de origen'];
    echo '<table class="form-table"><tbody>';
    foreach ($fields as $k => $label) {
        $v = (string) get_post_meta($post->ID, '_agui_' . $k, true);
        if ($v === '') {
            continue;
        }
        printf('<tr><th>%s</th><td>%s</td></tr>', esc_html($label), nl2br(esc_html($v)));
    }
    echo '</tbody></table>';
}

add_action('save_post', function ($post_id, $post) {
    if (!isset($_POST['agui_nonce']) || !wp_verify_nonce($_POST['agui_nonce'], 'agui_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    $in = isset($_POST['agui']) && is_array($_POST['agui']) ? wp_unslash($_POST['agui']) : [];

    $text = ['status', 'address', 'cards_title', 'badge_title', 'badge_text', 'rental_name', 'role', 'bg'];
    foreach ($text as $k) {
        if (array_key_exists($k, $in)) {
            update_post_meta($post_id, '_agui_' . $k, sanitize_text_field($in[$k]));
        }
    }
    foreach (['amenities', 'facts'] as $k) {
        if (array_key_exists($k, $in)) {
            update_post_meta($post_id, '_agui_' . $k, sanitize_textarea_field($in[$k]));
        }
    }
    foreach (['logo_id', 'brochure_id'] as $k) {
        if (array_key_exists($k, $in)) {
            update_post_meta($post_id, '_agui_' . $k, (int) $in[$k]);
        }
    }
    if (array_key_exists('gallery', $in)) {
        $ids = array_filter(array_map('intval', explode(',', (string) $in['gallery'])));
        update_post_meta($post_id, '_agui_gallery', implode(',', $ids));
    }
    if ($post->post_type === 'linea') {
        update_post_meta($post_id, '_agui_script_title', !empty($in['script_title']) ? '1' : '0');
        update_post_meta($post_id, '_agui_show_home', !empty($in['show_home']) ? '1' : '0');
    }
}, 10, 2);

/* ---------------------------------------------------------------------------------------------
 * Columnas de la lista de Consultas y de Líneas
 * ------------------------------------------------------------------------------------------- */

add_filter('manage_consulta_posts_columns', function () {
    return ['cb' => '<input type="checkbox">', 'title' => 'Consulta', 'tipo' => 'Tipo', 'proyecto' => 'Proyecto', 'email' => 'Email', 'telefono' => 'Teléfono', 'date' => 'Fecha'];
});
add_action('manage_consulta_posts_custom_column', function ($col, $id) {
    if (in_array($col, ['tipo', 'proyecto', 'email', 'telefono'], true)) {
        echo esc_html((string) get_post_meta($id, '_agui_' . $col, true));
    }
}, 10, 2);

add_filter('manage_linea_posts_columns', function ($cols) {
    $cols['agui_img'] = 'Foto';
    $cols['agui_status'] = 'Estado';
    return $cols;
});
add_action('manage_linea_posts_custom_column', function ($col, $id) {
    if ($col === 'agui_img') {
        echo get_the_post_thumbnail($id, [60, 60]);
    }
    if ($col === 'agui_status') {
        echo esc_html((string) get_post_meta($id, '_agui_status', true));
    }
}, 10, 2);

/** Aviso en el panel por cada consulta nueva sin leer: se muestra el contador en el menú. */
add_action('admin_menu', function () {
    global $menu;
    $count = (int) wp_count_posts('consulta')->private + (int) wp_count_posts('consulta')->publish;
    foreach ($menu as $i => $item) {
        if (isset($item[2]) && $item[2] === 'edit.php?post_type=consulta' && $count > 0) {
            $menu[$i][0] .= ' <span class="awaiting-mod">' . $count . '</span>';
        }
    }
}, 99);
