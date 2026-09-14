<?php

/**
 * Template Name: Simple
 * Template Post Type: page
 */

get_header();
common("header-nav");
?>

<main>
    <?php hero('page'); ?>

    <div class="layout-sidebar">
        <div class="main-sidebar">
            <?php
            component('anchor-nav', [
                'items' => [
                    ['label' => 'Section 1', 'target' => 'anchor-1'],
                    ['label' => 'Section 2', 'target' => '#anchor-2'],
                    ['label' => 'Section 3', 'target' => '#anchor-3'],
                ],
                'aria_label' =>  'Navigation des sections',
            ]);
            ?>
        </div>

        <div class="main-content">
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </div>
    </div>
</main>

<?php get_footer();
