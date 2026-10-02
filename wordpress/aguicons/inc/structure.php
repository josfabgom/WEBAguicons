<?php
/**
 * Estructura del sitio: portada, estadísticas, orden del carrusel, menú y pie, en una sola pantalla.
 */

if (!defined('ABSPATH')) {
    exit;
}

function agui_structure_page(): void
{
    $lines = get_posts(['post_type' => 'linea', 'post_status' => ['publish', 'draft'], 'post_parent' => 0, 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]);
    $pages = get_pages(['post_status' => 'publish', 'sort_column' => 'post_title']);
    $menu = wp_get_nav_menu_object('Principal');
    $items = $menu ? (array) wp_get_nav_menu_items($menu->term_id) : [];
    $saved = !empty($_GET['saved']);
    $hero = (int) agui_opt('hero_image');
    $field = function (string $key, string $label, string $help = '') {
        printf('<p><label><strong>%s</strong><input type="text" name="o[%s]" class="large-text" value="%s"></label>%s</p>', esc_html($label), esc_attr($key), esc_attr((string) agui_opt($key)), $help ? '<span class="description">' . esc_html($help) . '</span>' : '');
    };
    ?>
    <div class="wrap agui-panel agui-structure">
        <h1>Estructura del sitio</h1>
        <?php if ($saved) : ?><div class="notice notice-success is-dismissible"><p>Cambios guardados. <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Ver el sitio ↗</a></p></div><?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="agui_structure_save">
            <?php wp_nonce_field('agui_structure'); ?>

            <div class="agui-box">
                <h2>Portada del inicio</h2>
                <?php agui_field_image('o[hero_image]', $hero, 'Foto de portada', 'Foto grande de la cabecera del inicio.'); ?>
                <input type="hidden" name="o[hero_baked]" value="">
                <p><label><input type="checkbox" name="o[hero_baked]" value="1" <?php checked(agui_opt('hero_baked'), '1'); ?>> La imagen ya incluye el título</label><br><span class="description">Si está tildado, no se escribe el título encima de la foto (ya viene dibujado); el logo y el botón se mantienen.</span></p>
                <div class="agui-grid">
                    <?php $field('hero_before', 'Título: parte 1'); $field('hero_accent', 'Título: palabra en cursiva'); $field('hero_after', 'Título: parte 2'); $field('hero_button', 'Texto del botón'); $field('hero_url', 'Enlace del botón', 'Ej: /nosotros/'); ?>
                </div>
            </div>

            <div class="agui-box">
                <h2>Carrusel de líneas del inicio</h2>
                <?php $field('lines_title', 'Título de la sección'); ?>
                <p class="description">Arrastrá para cambiar el orden. Destildá para ocultar una línea del carrusel.</p>
                <ul class="agui-sortlist" id="agui-lines-order">
                    <?php foreach ($lines as $l) : $vis = get_post_meta($l->ID, '_agui_show_home', true) !== '0'; ?>
                        <li><span class="handle">☰</span><label><input type="checkbox" name="lines_visible[<?php echo (int) $l->ID; ?>]" value="1" <?php checked($vis); ?>> <?php echo esc_html($l->post_title); ?><?php echo $l->post_status !== 'publish' ? ' <em>(borrador)</em>' : ''; ?></label>
                            <input type="hidden" name="lines_order[]" value="<?php echo (int) $l->ID; ?>"></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="agui-box">
                <h2>Estadísticas</h2>
                <div class="agui-grid">
                    <?php for ($i = 1; $i <= 3; $i++) { $field("stat{$i}_value", "Dato $i: valor", $i === 1 ? 'Ej: +5.000' : ''); $field("stat{$i}_label", "Dato $i: etiqueta", $i === 1 ? 'Ej: Propietarios' : ''); } ?>
                </div>
                <p class="description">Íconos opcionales (si no cargás ninguno, se usan los del tema: llave, inversor y edificio).</p>
                <div class="agui-grid">
                    <?php for ($i = 1; $i <= 3; $i++) { agui_field_image("o[stat{$i}_icon]", (int) agui_opt("stat{$i}_icon"), "Ícono del dato $i"); } ?>
                </div>
            </div>

            <div class="agui-box">
                <h2>Menú principal</h2>
                <p class="description">Arrastrá para ordenar. Elegí una página existente o escribí una dirección. “↳ Sub-ítem” lo mete dentro del ítem principal de arriba (menú desplegable).</p>
                <ul class="agui-sortlist" id="agui-menu-rows">
                    <?php foreach ($items as $it) :
                        $is_page = $it->object === 'page';
                        ?>
                        <li class="agui-menu-row">
                            <span class="handle">☰</span>
                            <select name="menu_level[]" title="Nivel"><option value="0">Principal</option><option value="1" <?php selected((int) $it->menu_item_parent > 0); ?>>↳ Sub-ítem</option></select>
                            <input type="text" name="menu_label[]" value="<?php echo esc_attr($it->title); ?>" placeholder="Texto">
                            <select name="menu_target[]">
                                <option value="">— o dirección manual —</option>
                                <?php foreach ($pages as $pg) : ?><option value="page:<?php echo (int) $pg->ID; ?>" <?php selected($is_page && (int) $it->object_id === $pg->ID); ?>><?php echo esc_html($pg->post_title); ?></option><?php endforeach; ?>
                            </select>
                            <input type="text" name="menu_url[]" value="<?php echo $is_page ? '' : esc_attr($it->url); ?>" placeholder="https://…">
                            <button type="button" class="button-link agui-row-del">Quitar</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><button type="button" class="button" id="agui-menu-add">Agregar ítem al menú</button></p>
                <template id="agui-menu-tpl">
                    <li class="agui-menu-row"><span class="handle">☰</span><select name="menu_level[]" title="Nivel"><option value="0">Principal</option><option value="1">↳ Sub-ítem</option></select><input type="text" name="menu_label[]" placeholder="Texto">
                        <select name="menu_target[]"><option value="">— o dirección manual —</option><?php foreach ($pages as $pg) : ?><option value="page:<?php echo (int) $pg->ID; ?>"><?php echo esc_html($pg->post_title); ?></option><?php endforeach; ?></select>
                        <input type="text" name="menu_url[]" placeholder="https://…"><button type="button" class="button-link agui-row-del">Quitar</button></li>
                </template>
            </div>

            <div class="agui-box">
                <h2>Contacto y pie de página</h2>
                <div class="agui-grid">
                    <?php $field('address', 'Dirección'); $field('phone', 'Teléfono'); $field('email_contact', 'Email público'); $field('copyright', 'Copyright'); $field('powered', 'Crédito'); ?>
                </div>
            </div>

            <?php submit_button('Guardar la estructura'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_post_agui_structure_save', function () {
    check_admin_referer('agui_structure');
    if (!current_user_can('manage_options')) {
        wp_die('Sin permiso');
    }
    // Opciones (se mezclan con las existentes para no perder el resto)
    $in = isset($_POST['o']) && is_array($_POST['o']) ? wp_unslash($_POST['o']) : [];
    $opts = get_option('aguicons_opts', []);
    $opts = is_array($opts) ? $opts : [];
    foreach ($in as $k => $v) {
        $opts[sanitize_key($k)] = ($k === 'hero_image' || substr($k, -5) === '_icon') ? (int) $v : sanitize_text_field($v);
    }
    update_option('aguicons_opts', $opts);

    // Orden y visibilidad del carrusel
    $order = array_map('intval', (array) ($_POST['lines_order'] ?? []));
    $visible = (array) ($_POST['lines_visible'] ?? []);
    foreach ($order as $i => $lid) {
        wp_update_post(['ID' => $lid, 'menu_order' => $i + 1]);
        update_post_meta($lid, '_agui_show_home', isset($visible[$lid]) ? '1' : '0');
    }

    // Menú principal
    $labels = (array) wp_unslash($_POST['menu_label'] ?? []);
    $targets = (array) wp_unslash($_POST['menu_target'] ?? []);
    $urls = (array) wp_unslash($_POST['menu_url'] ?? []);
    $levels = (array) ($_POST['menu_level'] ?? []);
    $menu = wp_get_nav_menu_object('Principal');
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Principal');
    foreach ((array) wp_get_nav_menu_items($menu_id) as $it) {
        wp_delete_post($it->ID, true);
    }
    $pos = 1;
    $parent_item = 0;
    foreach ($labels as $i => $label) {
        $label = sanitize_text_field($label);
        $target = (string) ($targets[$i] ?? '');
        $url = esc_url_raw($urls[$i] ?? '');
        if ($label === '' || ($target === '' && $url === '')) {
            continue;
        }
        $is_sub = !empty($levels[$i]) && $parent_item;
        $common = ['menu-item-title' => $label, 'menu-item-status' => 'publish', 'menu-item-position' => $pos++, 'menu-item-parent-id' => $is_sub ? $parent_item : 0];
        if (strpos($target, 'page:') === 0) {
            $item_id = wp_update_nav_menu_item($menu_id, 0, $common + ['menu-item-object' => 'page', 'menu-item-object-id' => (int) substr($target, 5), 'menu-item-type' => 'post_type']);
        } else {
            $item_id = wp_update_nav_menu_item($menu_id, 0, $common + ['menu-item-url' => $url, 'menu-item-type' => 'custom']);
        }
        if (!$is_sub) {
            $parent_item = (int) $item_id;
        }
    }
    $locs = get_theme_mod('nav_menu_locations', []);
    $locs['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locs);

    wp_safe_redirect(admin_url('admin.php?page=aguicons-structure&saved=1'));
    exit;
});
