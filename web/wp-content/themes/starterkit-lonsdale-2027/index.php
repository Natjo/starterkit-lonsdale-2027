<?php
get_header();
common('header-nav');
?>

<main>
    <?php if (have_posts()) : ?>
        <div class="posts-list">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <h2>
                        <a href="<?= esc_url(get_permalink()) ?>">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
        </div>

        <?php component('pagination', [
            'total' => (int) ($GLOBALS['wp_query']->max_num_pages ?? 0),
            'current' => max(1, (int) get_query_var('paged')),
            'prev_text' => 'Précédent',
            'next_text' => 'Suivant',
            'aria_label' => 'Pagination des contenus',
        ]); ?>
    <?php else : ?>
        <p><?= esc_html__('Aucun contenu trouvé.', 'theme') ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
