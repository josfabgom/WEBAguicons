<?php
/** Novedades: listado de entradas del blog. */
get_header();
?>
<div class="agui-container agui-section">
    <h1 class="agui-h1">NOVEDADES</h1>
    <?php if (have_posts()) : ?>
        <div class="agui-posts">
            <?php while (have_posts()) : the_post(); ?>
                <article class="agui-post">
                    <?php if (has_post_thumbnail()) : ?><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('agui-slide', ['loading' => 'lazy']); ?></a><?php endif; ?>
                    <div class="agui-post-body">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <nav class="agui-pagination" aria-label="Paginación"><?php echo wp_kses_post(paginate_links(['prev_text' => '‹', 'next_text' => '›', 'type' => 'plain'])); ?></nav>
    <?php else : ?>
        <p style="text-align:center">Pronto publicaremos novedades de nuestros proyectos.</p>
    <?php endif; ?>
</div>
<?php
echo agui_contact_bar(true);
get_footer();
