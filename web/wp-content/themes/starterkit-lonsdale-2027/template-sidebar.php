<?php

/**
 * Template Name: Sidebar
 * Template Post Type: page
 */

$fields = theme_get_fields();
$strates = is_array($fields['strates'] ?? null) ? $fields['strates'] : [];
$anchor_items = [];
$seen_targets = [];

foreach ($strates as $strate) {
    if (!is_array($strate) || array_is_list($strate)) continue;

    $options = is_array($strate['options'] ?? null) && !array_is_list($strate['options'])
        ? $strate['options']
        : [];
    $name = $options['name'] ?? '';
    $name = is_scalar($name) || $name instanceof Stringable ? trim((string) $name) : '';
    $target = $name !== '' ? sanitize_title($name) : '';

    if ($target === '' || isset($seen_targets[$target])) continue;

    $seen_targets[$target] = true;
    $anchor_items[] = [
        'label' => $name,
        'target' => $target,
    ];
}

get_header();
common("header-nav");
?>

<main>
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
