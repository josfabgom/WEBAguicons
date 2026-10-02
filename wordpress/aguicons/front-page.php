<?php
/** Inicio: portada, carrusel de líneas, estadísticas y contacto. */
get_header();

echo agui_banner();
echo agui_lines_carousel();
echo agui_stats();

while (have_posts()) {
    the_post();
    if (trim(get_the_content())) {
        echo '<div class="agui-container agui-prose agui-section">';
        the_content();
        echo '</div>';
    }
}

echo agui_testimonials();
echo agui_contact_bar(false);

get_footer();
