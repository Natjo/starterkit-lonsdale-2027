<?php
/**
 * Template Name: Fexible
 * Template Post Type: page
 */

get_header();
common("header-nav");
?>

<main>
    <?php hero('page'); ?>
    
    <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
</main>

<?php get_footer();
