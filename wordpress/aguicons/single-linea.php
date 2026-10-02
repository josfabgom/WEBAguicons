<?php
/** Una línea o proyecto: cabecera negra, sub-proyectos, carrusel, ficha, brochure, consulta y otras líneas. */
get_header();

while (have_posts()) {
    the_post();
    $post = get_post();
    $m = fn($k) => get_post_meta($post->ID, '_agui_' . $k, true);

    echo agui_line_intro($post);

    $children = agui_lines(['post_parent' => $post->ID]);
    echo agui_cards((string) $m('cards_title'), $children);

    $gallery = array_filter(array_map('intval', explode(',', (string) $m('gallery'))));
    echo agui_carousel('CARRUSEL DE VISTAS', $gallery);

    echo agui_facts('FICHA TÉCNICA', (string) $m('status'), (string) $m('facts'), (string) $m('address'));
    echo agui_lead_form('brochure', $post->post_title, '', $post->ID);
    echo agui_lead_form('inquiry', $post->post_title, '', $post->ID);

    // Las líneas hijas muestran las otras líneas respecto de su línea principal.
    $root = $post->post_parent ? (int) get_post_ancestors($post)[count(get_post_ancestors($post)) - 1] : $post->ID;
    echo agui_other_lines($root);
    echo agui_contact_bar(true);
}

get_footer();
