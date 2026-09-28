<?php
get_header();
common('header-nav');
?>

<main>
    <?php hero('page', ['title' => get_the_title()]); ?>

    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('team-content'); ?>>
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </article>

        <?php common('related'); ?>
    <?php endwhile; ?>
</main>

<?php get_footer();
