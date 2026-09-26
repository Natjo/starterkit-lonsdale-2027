<?php

/**
 * Template Name: Flexible sidebar nav anchor
 * Template Post Type: page
 */

$fields = theme_get_fields();
$anchor_items = theme_get_strate_anchor_items($fields['strates'] ?? []);

get_header();
common('header-nav');
?>

<main class="template-flexible-sidebar-navanchor">
    <?php hero('page'); ?>

    <div class="layout-sidebar">
        <div class="main-sidebar">
            <?php
            component('anchor-nav', [
                'items' => $anchor_items,
                'aria_label' => 'Navigation des sections',
            ]);
            ?>
        </div>

        <div class="main-content">
            <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
        </div>
    </div>
</main>

<?php get_footer();
