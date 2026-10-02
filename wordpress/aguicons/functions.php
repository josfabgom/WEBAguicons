<?php
/**
 * Tema Aguicons – funciones principales.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AGUI_VERSION', '2.0.0');

require_once get_theme_file_path('inc/options.php');
require_once get_theme_file_path('inc/cpt.php');
require_once get_theme_file_path('inc/render.php');
require_once get_theme_file_path('inc/forms.php');
require_once get_theme_file_path('inc/importer.php');

add_action('after_setup_theme', function () {
    load_theme_textdomain('aguicons', get_theme_file_path('languages'));
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('style.css');
    register_nav_menus([
        'primary' => 'Menú principal',
        'footer' => 'Menú del pie de página',
    ]);
    add_image_size('agui-card', 700, 1400, false);
    add_image_size('agui-slide', 1400, 1400, false);
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'aguicons-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,500;1,700&family=Poppins:wght@400;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('aguicons-style', get_stylesheet_uri(), ['aguicons-fonts'], AGUI_VERSION);
    wp_enqueue_script('aguicons-app', get_theme_file_uri('assets/js/app.js'), [], AGUI_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
    wp_localize_script('aguicons-app', 'AGUI', [
        'ajax' => admin_url('admin-ajax.php'),
        'ga' => agui_opt('ga_id'),
        'privacy' => agui_privacy_url(),
    ]);
});

/** Subtítulo/privacidad: URL de la página de política de privacidad. */
function agui_privacy_url(): string
{
    $p = get_page_by_path('politica-de-privacidad');
    return $p ? get_permalink($p) : home_url('/politica-de-privacidad/');
}

/** Ícono de la pestaña del navegador. */
add_action('wp_head', function () {
    if (!has_site_icon()) {
        printf('<link rel="icon" type="image/png" href="%s">' . "\n", esc_url(get_theme_file_uri('assets/img/aguicons-isotipo-oro.png')));
    }
    $o = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Aguicons',
        'url' => home_url('/'),
        'email' => agui_opt('email_contact'),
        'telephone' => agui_opt('phone'),
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => agui_opt('address'), 'addressCountry' => 'AR'],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($o, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 5);

/** Meta descripción: extracto de la página/línea (si no hay un plugin de SEO). */
add_action('wp_head', function () {
    if (!is_singular() || defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
        return;
    }
    $desc = has_excerpt() ? get_the_excerpt() : wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_content', get_the_ID()))), 28);
    if ($desc) {
        printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
    }
}, 6);

add_filter('document_title_parts', function ($parts) {
    if (is_front_page()) {
        $parts['title'] = 'Aguicons';
        $parts['tagline'] = 'Construimos tu futuro';
    }
    return $parts;
});

/** Los extractos también se editan en las páginas. */
add_action('init', function () {
    add_post_type_support('page', 'excerpt');
});

/** Limpieza de lo que no usa el tema. */
remove_action('wp_head', 'wp_generator');
