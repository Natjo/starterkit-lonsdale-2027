<?php
get_header();
common("header-nav");
?>

<main>
    <?php while (have_posts()) : the_post(); ?>
        <?php hero('page'); ?>

        <article <?php post_class(); ?>>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </article>
    <?php endwhile ?>
</main>

<?php get_footer();
