<?php
/** Una entrada de Novedades. */
get_header();
while (have_posts()) {
    the_post();
    ?>
    <article class="agui-container agui-article agui-section">
        <h1 class="agui-h1"><?php the_title(); ?></h1>
        <p class="agui-meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
        <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } ?>
        <div class="agui-prose"><?php the_content(); ?></div>
    </article>
    <?php
    echo agui_contact_bar(true);
}
get_footer();
