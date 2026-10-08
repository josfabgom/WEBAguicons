<?php
/** Una línea o proyecto: cabecera negra, sub-proyectos, carrusel de vistas, otras líneas y contacto. */
get_header();

while (have_posts()) {
    the_post();
    $post = get_post();
    $m = fn($k) => get_post_meta($post->ID, '_agui_' . $k, true);

    echo agui_line_intro($post);

    $children = agui_lines(['post_parent' => $post->ID]);
    echo agui_cards((string) $m('cards_title'), $children);

    // Solo el carrusel de vistas (sin pestañas de video, ficha, unidades ni avance).
    $gallery = array_filter(array_map('intval', explode(',', (string) $m('gallery'))));
    echo agui_carousel('CARRUSEL DE VISTAS', $gallery, true);

    echo '<div class="agui-dark">';
    // Las líneas hijas muestran las otras líneas respecto de su línea principal.
    $root = $post->post_parent ? (int) get_post_ancestors($post)[count(get_post_ancestors($post)) - 1] : $post->ID;
    echo agui_other_lines($root);
    $is_rental = trim((string) $m('badge_title') . (string) $m('badge_text')) !== '';
    $alq = get_page_by_path('alquiler', OBJECT, 'page');
    $fallback = $is_rental && $alq ? get_permalink($alq) : ($post->post_parent ? get_permalink($post->post_parent) : home_url('/'));
    echo agui_contact_bar(true, true, $fallback);
    echo '</div>';
}

get_footer();
