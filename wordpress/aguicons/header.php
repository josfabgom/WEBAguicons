<?php
/** Cabecera del sitio. */
if (!defined('ABSPATH')) {
    exit;
}
$is_home = is_front_page();
$is_gold = is_page() && get_post_meta(get_queried_object_id(), '_agui_bg', true) === 'gold';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.className+=' js';</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="agui-skip" href="#contenido">Saltar al contenido</a>
<header class="agui-header<?php echo $is_home ? ' is-home' : ''; ?><?php echo $is_gold ? ' is-gold' : ''; ?>">
    <div class="agui-header-inner">
        <?php if ($is_home) : ?>
            <span></span>
        <?php else : ?>
            <a class="agui-header-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Aguicons - Inicio"><?php echo $is_gold ? agui_logo('full', 'blanco') : agui_logo('iso', 'oro'); ?></a>
        <?php endif; ?>

        <nav class="agui-nav" aria-label="Principal">
            <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'agui-menu', 'fallback_cb' => false, 'depth' => 2]); ?>
        </nav>

        <button type="button" class="agui-burger" aria-label="Abrir menú" aria-expanded="false" aria-controls="agui-mobile">
            <span></span><span></span><span></span>
        </button>
    </div>
    <nav id="agui-mobile" class="agui-mobile" aria-label="Menú móvil" hidden>
        <?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'agui-mobile-menu', 'fallback_cb' => false, 'depth' => 2]); ?>
        <p class="agui-mobile-title">LÍNEAS EDILICIAS</p>
        <ul class="agui-mobile-lines">
            <?php foreach (agui_lines(['post_parent' => 0]) as $l) : ?>
                <li><a href="<?php echo esc_url(get_permalink($l)); ?>"><?php echo esc_html($l->post_title); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>
<main id="contenido">
