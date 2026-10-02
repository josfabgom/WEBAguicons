<?php
get_header();
echo '<div class="agui-container agui-prose agui-section">';
if (have_posts()) {
    while (have_posts()) {
        the_post();
        echo '<h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2>';
        the_excerpt();
    }
} else {
    echo '<p>No hay contenido para mostrar.</p>';
}
echo '</div>';
echo agui_contact_bar(true);
get_footer();
