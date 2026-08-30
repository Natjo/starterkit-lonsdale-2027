<?php
get_header();
common("header-nav");
?>

<main>
    <?php hero('news'); ?>

    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('news-content'); ?>>
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </article>

        <?php common('related'); ?>
    <?php endwhile; ?>
</main>

<?php get_footer();
