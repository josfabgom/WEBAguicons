<?php
/** Pie del sitio, botón de WhatsApp y aviso de cookies. */
if (!defined('ABSPATH')) {
    exit;
}
$wa = preg_replace('/\D+/', '', (string) agui_opt('whatsapp'));
$topic = '';
if (is_singular(['linea']) || (is_page() && !is_front_page())) {
    $topic = get_the_title();
}
$wa_text = $topic ? 'Hola, quiero más información sobre ' . $topic . '.' : 'Hola, quiero más información sobre Aguicons.';
?>
</main>
<footer class="agui-footer">
    <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="Aguicons - Inicio"><?php echo agui_logo('full', 'oro', 'agui-footer-logo'); ?></a>
    <?php wp_nav_menu(['theme_location' => 'footer', 'container' => 'nav', 'container_class' => 'agui-footer-nav', 'container_aria_label' => 'Pie de página', 'menu_class' => 'agui-footer-menu', 'fallback_cb' => false, 'depth' => 1]); ?>
    <p class="agui-footer-copy"><?php echo esc_html(agui_opt('copyright')); ?> · <?php echo esc_html(agui_opt('powered')); ?></p>
</footer>

<?php if ($wa) : ?>
    <a class="agui-whatsapp" href="<?php echo esc_url('https://wa.me/' . $wa . '?text=' . rawurlencode($wa_text)); ?>" target="_blank" rel="noopener noreferrer" aria-label="Escribinos por WhatsApp" data-topic="<?php echo esc_attr($topic ?: 'home'); ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.2 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .5l-.4.6c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.8-.1 1.4Z"/></svg>
    </a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
