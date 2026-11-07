<?php
if (!post_type_exists('news')) return;

$excluded_ids = [];
if (is_singular('news')) {
    $current_id = get_queried_object_id();
    if ($current_id > 0) $excluded_ids[] = $current_id;
}

$items = get_posts([
    'post_type' => 'news',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'ids',
    'no_found_rows' => true,
    'ignore_sticky_posts' => true,
    'suppress_filters' => false,
    'post__not_in' => $excluded_ids,
]);
$items = array_values(array_filter($items, static fn (mixed $id): bool =>
    is_int($id) && $id > 0
));

if ($items === []) return;

theme_prime_posts($items);
$items = array_values(array_filter(array_map('theme_post_card_item', $items)));
if ($items === []) return;
?>

<section class="related" aria-labelledby="related-title">
    <div class="related-content">
        <header class="related-header">
            <?php component('title', [
                'title' => 'À découvrir',
                'hx' => 2,
                'classes' => 'title-2',
                'attributes' => ['id' => 'related-title'],
            ]); ?>

            <?php component('text', [
                'text' => '<p>Découvrez nos dernières actualités.</p>',
                'classes' => 'rte',
            ]); ?>
        </header>

        <?php component('list', [
            'items' => $items,
            'card' => 'news',
            'classes' => 'related-list',
        ]); ?>
    </div>
</section>
