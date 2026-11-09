<?php

/**
 * Template Name: Flexible nav anchor
 * Template Post Type: page
 */

$fields = theme_get_fields();
$anchor_items = theme_get_strate_anchor_items($fields['strates'] ?? []);

get_header();
common("header-nav");
?>

<main class="template-flexible-navanchor">
    <?php hero('page'); ?>

    <?php component('anchor-nav', [
        'items' => $anchor_items,
        'aria_label' => 'Navigation des sections',
        'horizontal' => true,
    ]); ?>

    <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
</main>

<?php get_footer();
