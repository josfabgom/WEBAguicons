<?php
/**
 * Asistente paso a paso para crear o completar una línea / sub-proyecto.
 */

if (!defined('ABSPATH')) {
    exit;
}

function agui_wizard_values(?WP_Post $p): array
{
    $m = fn($k, $d = '') => $p ? ((string) get_post_meta($p->ID, '_agui_' . $k, true) ?: $d) : $d;
    $paras = [];
    if ($p) {
        preg_match_all('#<p[^>]*>(.*?)</p>#s', (string) apply_filters('the_content', $p->post_content), $mm);
        $paras = array_map(fn($t) => trim(wp_strip_all_tags($t)), $mm[1] ?? []);
    }
    return [
        'id' => $p ? $p->ID : 0,
        'title' => $p ? $p->post_title : '',
        'parent' => $p ? $p->post_parent : 0,
        'status_post' => $p ? $p->post_status : 'draft',
        'order' => $p ? $p->menu_order : 0,
        'status' => $m('status'),
        'script_title' => $m('script_title') === '1',
        'logo_id' => (int) $m('logo_id'),
        'badge_title' => $m('badge_title'),
        'badge_text' => $m('badge_text'),
        'show_home' => $p ? ($m('show_home') !== '0') : true,
        'text' => implode("\n\n", $paras),
        'amenities' => $m('amenities'),
        'cards_title' => $m('cards_title'),
        'thumb_id' => $p ? (int) get_post_thumbnail_id($p) : 0,
        'gallery' => $m('gallery'),
        'facts' => $m('facts'),
        'address' => $m('address'),
        'coords' => $m('coords'),
        'video' => $m('video'),
        'units' => $m('units'),
        'brochure_id' => (int) $m('brochure_id'),
        'avance' => $m('avance'),
    ];
}

function agui_wizard_page(): void
{
    $line = (int) ($_GET['line'] ?? 0);
    $p = $line ? get_post($line) : null;
    if ($p && $p->post_type !== 'linea') {
        $p = null;
    }
    $v = agui_wizard_values($p);
    $parents = get_posts(['post_type' => 'linea', 'post_status' => ['publish', 'draft'], 'post_parent' => 0, 'numberposts' => -1, 'exclude' => $p ? [$p->ID] : [], 'orderby' => 'menu_order title', 'order' => 'ASC']);
    $gallery_ids = array_filter(array_map('intval', explode(',', $v['gallery'])));
    $avance = json_decode($v['avance'], true);
    $avance = is_array($avance) ? $avance : [];
    $steps = ['Datos básicos', 'Textos', 'Imágenes', 'Ficha y extras', 'Publicar'];
    ?>
    <div class="wrap agui-panel agui-wizard">
        <h1><?php echo $p ? 'Completar: ' . esc_html($p->post_title) : 'Nueva línea o proyecto'; ?></h1>
        <?php if (!empty($_GET['copied'])) : ?><div class="notice notice-success inline"><p>Se creó una copia en borrador. Cambiá el nombre y los datos que correspondan.</p></div><?php endif; ?>

        <ol class="agui-steps" role="tablist">
            <?php foreach ($steps as $i => $s) : ?>
                <li><button type="button" data-goto="<?php echo $i + 1; ?>" class="<?php echo $i === 0 ? 'is-current' : ''; ?>"><span><?php echo $i + 1; ?></span> <?php echo esc_html($s); ?></button></li>
            <?php endforeach; ?>
        </ol>

        <form id="agui-wizard" autocomplete="off">
            <input type="hidden" name="w[id]" value="<?php echo (int) $v['id']; ?>">

            <!-- Paso 1 -->
            <section class="agui-step" data-step="1">
                <h2>1. Datos básicos</h2>
                <div class="agui-grid">
                    <p><label><strong>Nombre de la línea o proyecto *</strong><input type="text" name="w[title]" class="large-text" value="<?php echo esc_attr($v['title']); ?>" placeholder="Ej: Baradise"></label></p>
                    <p><label><strong>¿Es un sub-proyecto de otra línea?</strong>
                        <select name="w[parent]"><option value="0">No, es una línea principal</option>
                            <?php foreach ($parents as $pr) : ?><option value="<?php echo (int) $pr->ID; ?>" <?php selected($v['parent'], $pr->ID); ?>>Sí, de: <?php echo esc_html($pr->post_title); ?></option><?php endforeach; ?>
                        </select></label>
                        <span class="description">Ej: “Benrow Acros” es un sub-proyecto de “Benrow”.</span></p>
                    <p><label><strong>Estado del proyecto</strong>
                        <select name="w[status]"><?php foreach (AGUI_STATUSES as $val => $lab) : ?><option value="<?php echo esc_attr($val); ?>" <?php selected($v['status'], $val); ?>><?php echo esc_html($lab); ?></option><?php endforeach; ?></select></label></p>
                    <p><label><strong>Orden en el carrusel</strong><input type="number" name="w[order]" value="<?php echo (int) $v['order']; ?>" class="small-text"></label>
                        <span class="description">Número menor = aparece primero.</span></p>
                </div>
                <?php agui_field_image('w[logo_id]', (int) $v['logo_id'], 'Logo de la línea', 'PNG con fondo transparente. Reemplaza al nombre escrito en la cabecera negra.'); ?>
                <p><label><input type="checkbox" name="w[script_title]" value="1" <?php checked($v['script_title']); ?>> Mostrar el nombre con tipografía decorativa (solo si no hay logo)</label></p>
                <p><label><input type="checkbox" name="w[show_home]" value="1" <?php checked($v['show_home']); ?>> Mostrar en el carrusel del inicio</label></p>
                <fieldset class="agui-box">
                    <legend>Cartel dorado (opcional, para alquileres)</legend>
                    <input type="text" name="w[badge_title]" value="<?php echo esc_attr($v['badge_title']); ?>" placeholder="ALQUILER">
                    <input type="text" name="w[badge_text]" value="<?php echo esc_attr($v['badge_text']); ?>" placeholder="Unidades / Oficinas / Local comercial">
                    <span class="description">Las líneas con cartel aparecen en la página de Alquiler.</span>
                </fieldset>
            </section>

            <!-- Paso 2 -->
            <section class="agui-step" data-step="2" hidden>
                <h2>2. Textos</h2>
                <p><label><strong>Descripción</strong><br>
                    <textarea name="w[text]" rows="10" class="large-text" placeholder="Escribí uno o varios párrafos. Dejá una línea en blanco entre párrafo y párrafo."><?php echo esc_textarea($v['text']); ?></textarea></label></p>
                <p><label><strong>Amenities</strong> (uno por línea)<br>
                    <textarea name="w[amenities]" rows="5" class="large-text" placeholder="Piscina&#10;Solarium&#10;Gimnasio"><?php echo esc_textarea($v['amenities']); ?></textarea></label></p>
                <p><label><strong>Título de las tarjetas de sub-proyectos</strong><br>
                    <input type="text" name="w[cards_title]" class="regular-text" value="<?php echo esc_attr($v['cards_title']); ?>" placeholder="PROTAGÓNICO (ACROS)"></label>
                    <span class="description">Solo si esta línea tiene sub-proyectos.</span></p>
            </section>

            <!-- Paso 3 -->
            <section class="agui-step" data-step="3" hidden>
                <h2>3. Imágenes</h2>
                <?php agui_field_image('w[thumb_id]', (int) $v['thumb_id'], 'Foto de la tarjeta (vertical)', 'Es la foto que aparece en el carrusel del inicio y en las tarjetas. Mejor una foto vertical del edificio.'); ?>
                <div class="agui-media-field agui-gallery" data-multiple="1">
                    <strong>Galería de vistas (carrusel)</strong>
                    <input type="hidden" name="w[gallery]" value="<?php echo esc_attr(implode(',', $gallery_ids)); ?>">
                    <ul class="agui-gallery-list">
                        <?php foreach ($gallery_ids as $gid) : ?><li data-id="<?php echo (int) $gid; ?>"><?php echo wp_get_attachment_image($gid, 'thumbnail'); ?><button type="button" class="agui-gallery-remove" aria-label="Quitar">×</button></li><?php endforeach; ?>
                    </ul>
                    <button type="button" class="button agui-media-pick">Agregar imágenes</button>
                    <p class="description">Podés elegir varias a la vez y arrastrar las miniaturas para ordenarlas.</p>
                </div>
            </section>

            <!-- Paso 4 -->
            <section class="agui-step" data-step="4" hidden>
                <h2>4. Ficha técnica y extras</h2>
                <p><label><strong>Datos de la ficha</strong> (uno por línea: <code>Dato: Valor</code>)<br>
                    <textarea name="w[facts]" rows="5" class="large-text" placeholder="Ubicación: Posadas, Misiones&#10;Superficie: 60 a 120 m²&#10;Unidades: 24&#10;Entrega: 2027"><?php echo esc_textarea($v['facts']); ?></textarea></label></p>
                <div class="agui-grid">
                    <p><label><strong>Dirección para el mapa</strong><input type="text" name="w[address]" class="large-text" value="<?php echo esc_attr($v['address']); ?>" placeholder="Costanera, Posadas, Misiones"></label></p>
                    <p><label><strong>Coordenadas</strong> (latitud, longitud)<input type="text" name="w[coords]" class="regular-text" value="<?php echo esc_attr($v['coords']); ?>" placeholder="-27.3671, -55.8961"></label></p>
                    <p><label><strong>Video o recorrido 360°</strong><input type="url" name="w[video]" class="large-text" value="<?php echo esc_attr($v['video']); ?>" placeholder="https://www.youtube.com/watch?v=…"></label></p>
                </div>
                <p><label><strong>Unidades disponibles</strong> (<code>Tipología | Superficie | Precio | Estado</code>)<br>
                    <textarea name="w[units]" rows="5" class="large-text code" placeholder="2 dormitorios | 85 m² | USD 120.000 | Disponible"><?php echo esc_textarea($v['units']); ?></textarea></label></p>
                <?php agui_field_file('w[brochure_id]', (int) $v['brochure_id'], 'Brochure en PDF', 'Si no se carga, al pedirlo se avisa que llegará por email.'); ?>
                <h3>Avance de obra</h3>
                <div id="agui-avance" data-rows="<?php echo esc_attr(wp_json_encode($avance)); ?>"></div>
                <input type="hidden" name="w[avance]" id="agui-avance-json" value="<?php echo esc_attr($v['avance']); ?>">
                <p><button type="button" class="button" id="agui-avance-add">Agregar entrada de avance</button></p>
            </section>

            <!-- Paso 5 -->
            <section class="agui-step" data-step="5" hidden>
                <h2>5. Publicar</h2>
                <div id="agui-check" class="agui-box"><p>Guardá para ver el resumen de lo que falta.</p></div>
                <p class="agui-publish">
                    <button type="button" class="button" data-save="draft">Guardar como borrador</button>
                    <button type="button" class="button button-primary button-hero" data-save="publish"><?php echo $v['status_post'] === 'publish' ? 'Guardar cambios' : 'Publicar'; ?></button>
                    <a class="button" id="agui-view" href="#" target="_blank" rel="noopener" hidden>Ver la página ↗</a>
                </p>
            </section>

            <div class="agui-nav">
                <button type="button" class="button" id="agui-prev" hidden>← Anterior</button>
                <button type="button" class="button button-primary" id="agui-next">Siguiente →</button>
                <button type="button" class="button" data-save="draft">Guardar borrador</button>
                <span id="agui-msg" role="status"></span>
            </div>
        </form>
    </div>
    <?php
}

/** Guarda los campos del asistente en la línea. */
function agui_wizard_apply(int $id, array $w): void
{
    $txt = fn($k) => sanitize_text_field($w[$k] ?? '');
    $area = fn($k) => sanitize_textarea_field($w[$k] ?? '');
    foreach (['status', 'cards_title', 'badge_title', 'badge_text', 'address'] as $k) {
        update_post_meta($id, '_agui_' . $k, $txt($k));
    }
    update_post_meta($id, '_agui_amenities', $area('amenities'));
    update_post_meta($id, '_agui_facts', $area('facts'));
    update_post_meta($id, '_agui_units', $area('units'));
    update_post_meta($id, '_agui_video', esc_url_raw($w['video'] ?? ''));
    update_post_meta($id, '_agui_coords', trim(preg_replace('/[^0-9.,\-\s]/', '', (string) ($w['coords'] ?? ''))));
    update_post_meta($id, '_agui_script_title', !empty($w['script_title']) ? '1' : '0');
    update_post_meta($id, '_agui_show_home', !empty($w['show_home']) ? '1' : '0');
    update_post_meta($id, '_agui_logo_id', (int) ($w['logo_id'] ?? 0));
    update_post_meta($id, '_agui_brochure_id', (int) ($w['brochure_id'] ?? 0));
    update_post_meta($id, '_agui_gallery', implode(',', array_filter(array_map('intval', explode(',', (string) ($w['gallery'] ?? ''))))));

    $rows = json_decode((string) ($w['avance'] ?? ''), true);
    $clean = [];
    foreach (is_array($rows) ? $rows : [] as $r) {
        $d = sanitize_text_field($r['date'] ?? '');
        $t = sanitize_text_field($r['text'] ?? '');
        $ids = array_values(array_filter(array_map('intval', (array) ($r['ids'] ?? []))));
        if ($d !== '' || $t !== '' || $ids) {
            $clean[] = ['date' => $d, 'text' => $t, 'ids' => $ids];
        }
    }
    update_post_meta($id, '_agui_avance', wp_json_encode($clean, JSON_UNESCAPED_UNICODE));

    $thumb = (int) ($w['thumb_id'] ?? 0);
    if ($thumb) {
        set_post_thumbnail($id, $thumb);
    } else {
        delete_post_thumbnail($id);
    }
}

add_action('wp_ajax_agui_wizard_save', function () {
    check_ajax_referer('agui_wizard', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permiso', 403);
    }
    $w = isset($_POST['w']) && is_array($_POST['w']) ? wp_unslash($_POST['w']) : [];
    $title = sanitize_text_field($w['title'] ?? '');
    if ($title === '') {
        wp_send_json_error('Escribí el nombre de la línea (paso 1).');
    }
    $status = ($_POST['status'] ?? 'draft') === 'publish' ? 'publish' : 'draft';
    $id = (int) ($w['id'] ?? 0);

    $content = '';
    foreach (array_filter(array_map('trim', preg_split("/\n\s*\n/", (string) ($w['text'] ?? '')))) as $para) {
        $content .= agui_b_para(sanitize_textarea_field($para));
    }
    $args = [
        'post_type' => 'linea', 'post_title' => $title, 'post_content' => $content, 'post_status' => $status,
        'post_parent' => (int) ($w['parent'] ?? 0), 'menu_order' => (int) ($w['order'] ?? 0),
        'post_excerpt' => wp_trim_words(wp_strip_all_tags($content), 28),
    ];
    if ($id && get_post($id)) {
        $args['ID'] = $id;
        // Un borrador guardado desde "Guardar borrador" no despublica una línea ya publicada.
        if ($status === 'draft' && get_post_status($id) === 'publish' && ($_POST['keep'] ?? '') === '1') {
            $args['post_status'] = 'publish';
        }
        $id = wp_update_post($args, true);
    } else {
        $id = wp_insert_post($args, true);
    }
    if (is_wp_error($id)) {
        wp_send_json_error($id->get_error_message());
    }
    agui_wizard_apply((int) $id, $w);

    $post = get_post($id);
    [$score, $missing] = agui_line_check($post);
    wp_send_json_success([
        'id' => (int) $id,
        'status' => $post->post_status,
        'permalink' => $post->post_status === 'publish' ? get_permalink($post) : get_preview_post_link($post),
        'score' => $score,
        'missing' => $missing,
    ]);
});
