<?php
/**
 * Template Name: Simple
 * Template Post Type: page
 */

get_header();
common("header-nav");
$fields = theme_get_fields();

?>

<main>
    <?php hero('page'); ?>
    
    <?php strate("text",$fields); ?>
</main>

<?php get_footer();
