<?php

/**
 * Template Name: Flexible sidebar
 * Template Post Type: page
 */


get_header();
common('header-nav');
?>

<main class="template-flexible-sidebar">
    <?php hero('page'); ?>

    <div class="layout-sidebar">
        <aside class="main-sidebar"></aside>

        <div class="main-content">
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </div>
    </div>
</main>

<?php get_footer();
