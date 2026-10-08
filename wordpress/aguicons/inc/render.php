<?php
/**
 * Piezas visuales del sitio. Se usan desde las plantillas y como shortcodes dentro de cualquier página.
 */

if (!defined('ABSPATH')) {
    exit;
}

function agui_logo(string $variant = 'full', string $tone = 'oro', string $class = ''): string
{
    // 'full' = isotipo + nombre, 'iso' = solo el triángulo, 'name' = solo el nombre AGUICONS
    $file = $variant === 'name' ? "aguicons-nombre-{$tone}.png" : 'aguicons' . ($variant === 'iso' ? '-isotipo' : '') . '-' . $tone . '.png';
    [$w, $h] = $variant === 'name' ? [712, 104] : [700, $variant === 'iso' ? 606 : 644];
    return sprintf('<img src="%s" alt="Aguicons" width="%d" height="%d" class="%s">', esc_url(get_theme_file_uri('assets/img/' . $file)), $w, $h, esc_attr($class));
}

function agui_img(int $id, string $size = 'large', array $attr = []): string
{
    if (!$id) {
        return '';
    }
    return wp_get_attachment_image($id, $size, false, array_merge(['loading' => 'lazy', 'decoding' => 'async'], $attr));
}

function agui_href(string $url): string
{
    return preg_match('#^(https?:|mailto:|tel:|\#)#', $url) ? $url : home_url('/' . ltrim($url, '/'));
}

function agui_lines(array $args = []): array
{
    return get_posts(array_merge([
        'post_type' => 'linea',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ], $args));
}

function agui_badge(int $post_id): string
{
    $t = (string) get_post_meta($post_id, '_agui_badge_title', true);
    $x = (string) get_post_meta($post_id, '_agui_badge_text', true);
    if ($t === '' && $x === '') {
        return '';
    }
    return '<span class="agui-badge"><strong>' . esc_html($t) . '</strong>' . ($x !== '' ? '<em>' . esc_html($x) . '</em>' : '') . '</span>';
}

/* --- Portada ------------------------------------------------------------------------------- */

function agui_banner(): string
{
    $img = (int) agui_opt('hero_image');
    if (!$img) {
        return '';
    }
    // Si la foto ya trae el título dibujado, no se vuelve a escribir encima (queda solo para lectores de pantalla).
    $has_title = agui_opt('hero_baked') === '1';
    $title = trim(agui_opt('hero_before') . ' ' . agui_opt('hero_accent') . ' ' . agui_opt('hero_after'));
    ob_start();
    ?>
    <section class="agui-banner<?php echo $has_title ? ' has-title' : ''; ?><?php echo agui_opt('hero_logo') === '1' ? ' has-logo' : ''; ?>">
        <?php echo wp_get_attachment_image($img, 'full', false, ['class' => 'agui-banner-img', 'fetchpriority' => 'high', 'alt' => $has_title ? 'Aguicons - ' . $title : 'Aguicons']); ?>
        <div class="agui-banner-fade"></div>
        <?php if (agui_opt('hero_logo') === '1') : ?>
            <div class="agui-banner-logo"><?php echo agui_logo('name', 'oro'); ?></div>
        <?php endif; ?>
        <?php if ($has_title) : ?>
            <h1 class="screen-reader-text"><?php echo esc_html($title); ?></h1>
        <?php else : ?>
            <h1 class="agui-banner-title"><?php echo esc_html(agui_opt('hero_before')); ?> <em><?php echo esc_html(agui_opt('hero_accent')); ?></em> <?php echo esc_html(agui_opt('hero_after')); ?></h1>
        <?php endif; ?>
        <?php if (agui_opt('hero_button')) : ?>
            <div class="agui-banner-btn"><a href="<?php echo esc_url(agui_href(agui_opt('hero_url'))); ?>"><?php echo esc_html(agui_opt('hero_button')); ?></a></div>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Carrusel de líneas (inicio) ----------------------------------------------------------- */

function agui_lines_carousel(string $title = ''): string
{
    $title = $title !== '' ? $title : agui_opt('lines_title');
    $lines = array_filter(agui_lines(['post_parent' => 0]), fn($p) => get_post_meta($p->ID, '_agui_show_home', true) !== '0');
    if (!$lines) {
        return '';
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container">
            <?php if ($title) : ?><h2 class="agui-h2"><?php echo esc_html($title); ?></h2><?php endif; ?>
            <div class="agui-carousel-wrap">
                <button type="button" class="agui-arrow agui-arrow-prev" aria-label="Anterior">‹</button>
                <button type="button" class="agui-arrow agui-arrow-next" aria-label="Siguiente">›</button>
                <div class="agui-carousel agui-lines-track" data-speed="70">
                    <?php foreach ($lines as $p) : ?>
                        <a class="agui-line-card" href="<?php echo esc_url(get_permalink($p)); ?>">
                            <?php if (has_post_thumbnail($p)) {
                                echo get_the_post_thumbnail($p, 'agui-card', ['loading' => 'lazy', 'decoding' => 'async', 'alt' => $p->post_title, 'draggable' => 'false']);
                            } ?>
                            <span class="agui-line-name"><?php
                                $lg = (int) get_post_meta($p->ID, '_agui_logo_id', true);
                                echo $lg ? agui_img($lg, 'medium', ['alt' => $p->post_title, 'draggable' => 'false']) : esc_html($p->post_title);
                            ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Estadísticas -------------------------------------------------------------------------- */

function agui_stats(): string
{
    $defaults = ['stat-llave', 'stat-inversor', 'stat-edificio'];
    $rows = [];
    for ($i = 1; $i <= 3; $i++) {
        $v = agui_opt("stat{$i}_value");
        $l = agui_opt("stat{$i}_label");
        if ($v !== '') {
            $rows[] = [$v, $l, (int) agui_opt("stat{$i}_icon"), $defaults[$i - 1]];
        }
    }
    if (!$rows) {
        return '';
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container">
            <h2 class="agui-h2">Estadísticas</h2>
            <div class="agui-stats">
                <?php foreach ($rows as [$v, $l, $icon, $default]) : ?>
                    <div class="agui-stat">
                        <?php if ($icon) : ?>
                            <?php echo agui_img($icon, 'thumbnail', ['class' => 'agui-stat-icon', 'alt' => '', 'loading' => 'lazy']); ?>
                        <?php else : ?>
                            <img class="agui-stat-icon" src="<?php echo esc_url(get_theme_file_uri('assets/img/' . $default . '.png')); ?>" alt="" width="60" height="60" loading="lazy">
                        <?php endif; ?>
                        <div class="agui-stat-text"><strong data-count="<?php echo esc_attr($v); ?>"><?php echo esc_html($v); ?></strong><span><?php echo esc_html($l); ?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Barra de contacto --------------------------------------------------------------------- */

function agui_contact_bar(bool $back = true, bool $dark = false, string $back_url = ''): string
{
    ob_start();
    ?>
    <section class="agui-contactbar agui-reveal<?php echo $dark ? ' is-dark' : ''; ?>">
        <?php if (agui_opt('address')) : ?><p class="agui-address">📍 <?php echo esc_html(agui_opt('address')); ?></p><?php endif; ?>
        <a class="agui-btn" href="<?php echo esc_url(home_url('/contacto/')); ?>">CONVERSÁ CON NOSOTROS</a>
        <p class="agui-small"><a href="mailto:<?php echo esc_attr(agui_opt('email_contact')); ?>"><?php echo esc_html(agui_opt('email_contact')); ?></a> - <a href="tel:+<?php echo esc_attr(preg_replace('/\D+/', '', (string) agui_opt('phone'))); ?>"><?php echo esc_html(agui_opt('phone')); ?></a></p>
        <?php if ($back) : ?><a class="agui-btn agui-btn-outline agui-back" href="<?php echo esc_url($back_url !== '' ? $back_url : home_url('/')); ?>">VOLVER</a><?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Cabecera negra de una línea ----------------------------------------------------------- */

function agui_line_intro(WP_Post $post): string
{
    $m = fn($k) => get_post_meta($post->ID, '_agui_' . $k, true);
    $logo = (int) $m('logo_id');
    $amen = array_filter(array_map('trim', preg_split('/\R/', (string) $m('amenities'))));
    preg_match_all('#<p[^>]*>(.*?)</p>#s', (string) apply_filters('the_content', $post->post_content), $mm);
    $paras = array_filter(array_map(fn($t) => trim(wp_strip_all_tags($t)), $mm[1] ?? []));
    ob_start();
    ?>
    <section class="agui-intro">
        <div class="agui-intro-inner">
            <?php if ($logo) : ?>
                <?php echo agui_img($logo, 'medium', ['class' => 'agui-intro-wordmark', 'alt' => $post->post_title, 'loading' => 'eager']); ?>
            <?php else : ?>
                <h1 class="agui-intro-title<?php echo $m('script_title') === '1' ? ' is-script' : ''; ?>"><?php echo esc_html($post->post_title); ?></h1>
            <?php endif; ?>
            <div class="agui-intro-text"><?php foreach ($paras as $p) : ?><p><?php echo esc_html($p); ?></p><?php endforeach; ?></div>
            <?php if ($amen) : ?>
                <ul class="agui-amenities"><?php foreach ($amen as $a) : ?><li><?php echo esc_html($a); ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Tarjetas protagónicas (sub-proyectos) ------------------------------------------------- */

function agui_cards(string $title, array $posts, bool $whatsapp = false): string
{
    if (!$posts) {
        return '';
    }
    $wa = preg_replace('/D+/', '', (string) agui_opt('whatsapp'));
    ob_start();
    ?>
    <section class="agui-cardsband agui-reveal<?php echo $title ? '' : ' is-flush'; ?>">
        <?php if ($title) : ?><div class="agui-container"><h2 class="agui-h2"><?php echo esc_html($title); ?></h2></div><?php endif; ?>
        <div class="agui-cardsdark">
            <div class="agui-container">
                <div class="agui-cards">
                    <?php foreach ($posts as $p) : ?>
                        <?php
                        $href = get_permalink($p);
                        $extra = '';
                        if ($whatsapp && $wa) {
                            $href = 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hola, quiero consultar por el alquiler de ' . $p->post_title . '.');
                            $extra = ' target="_blank" rel="noopener noreferrer"';
                        }
                        ?>
                        <a class="agui-card" href="<?php echo esc_url($href); ?>"<?php echo $extra; ?>>
                            <?php if (has_post_thumbnail($p)) {
                                echo get_the_post_thumbnail($p, 'agui-card', ['loading' => 'lazy', 'decoding' => 'async', 'alt' => $p->post_title]);
                            } ?>
                            <span class="agui-card-name"><?php echo esc_html($p->post_title); ?></span>
                            <?php echo agui_badge($p->ID); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Carrusel de vistas (con ampliación) --------------------------------------------------- */

function agui_carousel(string $title, array $ids, bool $narrow = false): string
{
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) {
        return '';
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container<?php echo $narrow ? ' agui-prose agui-carousel-page' : ''; ?>">
            <?php if ($title) : ?><h2 class="agui-h2"><?php echo esc_html($title); ?></h2><?php endif; ?>
            <div class="agui-carousel-wrap">
                <button type="button" class="agui-arrow agui-arrow-prev" aria-label="Anterior">‹</button>
                <button type="button" class="agui-arrow agui-arrow-next" aria-label="Siguiente">›</button>
            <div class="agui-carousel agui-gallery-track" data-speed="120">
                <?php foreach ($ids as $i => $id) :
                    $full = wp_get_attachment_image_url($id, 'full');
                    ?>
                    <button type="button" class="agui-slide" data-full="<?php echo esc_url($full); ?>" aria-label="Ampliar imagen <?php echo $i + 1; ?>">
                        <?php echo agui_img($id, 'agui-slide', ['draggable' => 'false', 'loading' => $i < 2 ? 'eager' : 'lazy']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Ficha técnica + mapa ------------------------------------------------------------------ */

function agui_facts(string $title, string $status, string $facts_text, string $address): string
{
    $facts = [];
    foreach (array_filter(array_map('trim', preg_split('/\R/', $facts_text))) as $line) {
        $parts = array_map('trim', explode(':', $line, 2));
        $val = $parts[1] ?? '';
        // Los datos de relleno ("A confirmar") o vacíos no se muestran.
        if ($val === '' || preg_match('/^a\s+confirmar\.?$/iu', $val)) {
            continue;
        }
        $facts[] = [$parts[0], $val];
    }
    if (!$facts && !$status && !$address) {
        return '';
    }
    $q = rawurlencode($address);
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container agui-narrow">
            <?php if ($title) : ?><h2 class="agui-h2"><?php echo esc_html($title); ?></h2><?php endif; ?>
            <div class="agui-facts-grid">
                <div>
                    <?php if ($status) : ?><p><span class="agui-status"><?php echo esc_html($status); ?></span></p><?php endif; ?>
                    <?php if ($facts) : ?>
                        <dl class="agui-facts">
                            <?php foreach ($facts as [$k, $v]) : ?><div><dt><?php echo esc_html($k); ?></dt><dd><?php echo esc_html($v); ?></dd></div><?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                </div>
                <?php if ($address) : ?>
                    <div>
                        <iframe title="Mapa: <?php echo esc_attr($address); ?>" src="https://www.google.com/maps?q=<?php echo $q; ?>&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <a href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo $q; ?>" target="_blank" rel="noopener noreferrer">Ver en Google Maps</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Formularios (consulta / brochure / contacto) ------------------------------------------ */

function agui_lead_form(string $mode, string $project, string $title = '', int $line_id = 0): string
{
    $brochure = $mode === 'brochure';
    $contact = $mode === 'contact';
    if ($title === '') {
        $title = $brochure ? 'Descargá el brochure de ' . $project : ($contact ? 'Conversá con nosotros' : 'Quiero información de ' . $project);
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container agui-form-wrap">
            <h2 class="agui-h2"><?php echo esc_html($title); ?></h2>
            <form class="agui-form" method="post" novalidate data-mode="<?php echo esc_attr($mode); ?>">
                <input type="hidden" name="mode" value="<?php echo esc_attr($mode); ?>">
                <input type="hidden" name="proyecto" value="<?php echo esc_attr($project); ?>">
                <input type="hidden" name="line_id" value="<?php echo (int) $line_id; ?>">
                <input type="hidden" name="pagina" value="<?php echo esc_attr(get_permalink() ?: home_url('/')); ?>">
                <input type="hidden" name="t" value="<?php echo esc_attr((string) time()); ?>">
                <label>Nombre y apellido*<input name="nombre" required autocomplete="name"></label>
                <label>Email*<input name="email" type="email" required autocomplete="email"></label>
                <?php if (!$brochure) : ?>
                    <label>Teléfono<input name="telefono" type="tel" autocomplete="tel"></label>
                    <label>Mensaje<?php echo $contact ? '*' : ''; ?><textarea name="mensaje" rows="4" <?php echo $contact ? 'required' : ''; ?>></textarea></label>
                <?php endif; ?>
                <input name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="agui-hp">
                <p class="agui-small">Al enviar aceptás nuestra <a href="<?php echo esc_url(agui_privacy_url()); ?>">política de privacidad</a>.</p>
                <p class="agui-form-error" role="alert" hidden>No pudimos enviar el formulario. Probá de nuevo o escribinos por WhatsApp.</p>
                <button type="submit" class="agui-btn"><?php echo $brochure ? 'RECIBIR BROCHURE' : ($contact ? 'ENVIAR' : 'ENVIAR CONSULTA'); ?></button>
            </form>
            <div class="agui-form-done" role="status" hidden>
                <p><strong>¡Gracias! Recibimos tus datos.</strong></p>
                <p class="agui-form-done-text"></p>
                <a class="agui-btn agui-form-download" href="#" download hidden>DESCARGAR BROCHURE</a>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Testimonios --------------------------------------------------------------------------- */

function agui_testimonials(string $title = 'LO QUE DICEN NUESTROS CLIENTES'): string
{
    $items = get_posts(['post_type' => 'testimonio', 'numberposts' => -1, 'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC']]);
    if (!$items) {
        return '';
    }
    ob_start();
    ?>
    <section class="agui-section agui-reveal">
        <div class="agui-container">
            <h2 class="agui-h2"><?php echo esc_html($title); ?></h2>
            <div class="agui-testimonials">
                <?php foreach ($items as $t) : ?>
                    <figure class="agui-testimonial">
                        <blockquote>“<?php echo esc_html(wp_strip_all_tags($t->post_content)); ?>”</blockquote>
                        <figcaption>
                            <?php echo has_post_thumbnail($t) ? get_the_post_thumbnail($t, [80, 80], ['class' => 'agui-avatar', 'alt' => $t->post_title]) : ''; ?>
                            <span><strong><?php echo esc_html($t->post_title); ?></strong>
                                <?php $role = get_post_meta($t->ID, '_agui_role', true); echo $role ? '<br>' . esc_html($role) : ''; ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

/* --- Accesos a otras líneas ---------------------------------------------------------------- */

function agui_other_lines(int $root_id): string
{
    $lines = agui_lines(['post_parent' => 0, 'exclude' => [$root_id]]);
    if (!$lines) {
        return '';
    }
    ob_start();
    ?>
    <nav class="agui-section agui-otherlines agui-reveal" aria-label="Otras líneas">
        <h2 class="agui-h3">OTRAS LÍNEAS</h2>
        <ul><?php foreach ($lines as $p) : ?><li><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html($p->post_title); ?></a></li><?php endforeach; ?></ul>
    </nav>
    <?php
    return ob_get_clean();
}

/* --- Alquiler: todas las líneas con cartel ------------------------------------------------- */

function agui_rentals(): string
{
    $posts = array_filter(agui_lines(), fn($p) => get_post_meta($p->ID, '_agui_badge_title', true) !== '' || get_post_meta($p->ID, '_agui_badge_text', true) !== '');
    $ids = [];
    foreach ($posts as $p) {
        if (has_post_thumbnail($p)) {
            $ids[] = get_post_thumbnail_id($p);
        }
    }
    return agui_cards('DISPONIBLES', array_values($posts), true);
}

/* --- Mapa suelto --------------------------------------------------------------------------- */

function agui_map(string $title, string $address): string
{
    return agui_facts($title, '', "Dirección: " . agui_opt('address') . "\nEmail: " . agui_opt('email_contact') . "\nTeléfono / WhatsApp: " . agui_opt('phone'), $address);
}

/* --- Shortcodes (para usar en cualquier página del editor) --------------------------------- */

add_shortcode('aguicons_contacto_form', fn() => agui_lead_form('contact', 'Contacto'));
add_shortcode('aguicons_consulta', fn($a) => agui_lead_form('inquiry', (string) (shortcode_atts(['proyecto' => get_the_title()], $a)['proyecto'])));
add_shortcode('aguicons_brochure', fn($a) => agui_lead_form('brochure', (string) (shortcode_atts(['proyecto' => get_the_title()], $a)['proyecto'])));
add_shortcode('aguicons_mapa', function ($a) {
    $a = shortcode_atts(['titulo' => 'DÓNDE ESTAMOS', 'direccion' => ''], $a);
    return agui_map($a['titulo'], $a['direccion'] ?: agui_opt('map_query'));
});
add_shortcode('aguicons_testimonios', fn() => agui_testimonials());
add_shortcode('aguicons_alquiler', fn() => agui_rentals());
add_shortcode('aguicons_contacto', fn($a) => agui_contact_bar(($a['volver'] ?? '1') !== '0'));
add_shortcode('aguicons_lineas', fn() => agui_lines_carousel());
add_shortcode('aguicons_estadisticas', fn() => agui_stats());

/* --- Pestañas de una línea: vistas, video, ficha, unidades y avance en un solo bloque -------- */

function agui_line_tabs(WP_Post $post): string
{
    $m = fn($k) => (string) get_post_meta($post->ID, '_agui_' . $k, true);
    $gallery = array_filter(array_map('intval', explode(',', $m('gallery'))));
    $panels = array_filter([
        'Vistas' => agui_carousel('', $gallery),
        'Video / 360°' => agui_video($m('video'), ''),
        'Ficha técnica y mapa' => agui_facts('', $m('status'), $m('facts'), $m('address')),
        'Unidades' => agui_units($post),
        'Avance de obra' => agui_progress($post),
    ]);
    if (!$panels) {
        return '';
    }
    if (count($panels) === 1) {
        return reset($panels);
    }
    $uid = 'agui-tabs-' . $post->ID;
    ob_start();
    ?>
    <section class="agui-section agui-tabs agui-reveal" data-tabs>
        <div class="agui-container">
            <div class="agui-tablist" role="tablist" aria-label="Información del proyecto">
                <?php $i = 0; foreach ($panels as $label => $html) : ?>
                    <button type="button" role="tab" id="<?php echo esc_attr($uid . '-t' . $i); ?>" aria-controls="<?php echo esc_attr($uid . '-p' . $i); ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>" tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"><?php echo esc_html($label); ?></button>
                <?php $i++; endforeach; ?>
            </div>
            <?php $i = 0; foreach ($panels as $label => $html) : ?>
                <div class="agui-tabpanel" role="tabpanel" id="<?php echo esc_attr($uid . '-p' . $i); ?>" aria-labelledby="<?php echo esc_attr($uid . '-t' . $i); ?>" <?php echo $i === 0 ? '' : 'hidden'; ?>><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
            <?php $i++; endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

add_shortcode('aguicons_galeria', function ($a) {
    $a = shortcode_atts(['pagina' => '', 'titulo' => ''], $a);
    $p = $a['pagina'] ? get_page_by_path($a['pagina'], OBJECT, 'page') : get_post();
    if (!$p) {
        return '';
    }
    $ids = array_filter(array_map('intval', explode(',', (string) get_post_meta($p->ID, '_agui_gallery', true))));
    return agui_carousel((string) $a['titulo'], $ids, true);
});
