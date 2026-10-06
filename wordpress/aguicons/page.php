<?php
/** Páginas: el cuerpo se edita con el editor de WordPress (y shortcodes de Aguicons). */
get_header();

while (have_posts()) {
    the_post();
    $gold = get_post_meta(get_the_ID(), '_agui_bg', true) === 'gold';
    ?>
    <article class="agui-page<?php echo $gold ? ' is-gold' : ''; ?>">
        <div class="agui-container agui-prose agui-section">
            <?php the_content(); ?>
        </div>
        <?php
        $gallery = array_filter(array_map('intval', explode(',', (string) get_post_meta(get_the_ID(), '_agui_gallery', true))));
        echo agui_carousel('', $gallery, true);
        ?>
        <?php echo agui_contact_bar(true); ?>
    </article>
    <?php
}

get_footer();
