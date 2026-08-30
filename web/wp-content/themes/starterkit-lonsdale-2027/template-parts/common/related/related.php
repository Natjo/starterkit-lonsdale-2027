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

update_meta_cache('post', $items);

$items = array_map(static function (int $post_id): array {
    $hero = function_exists('get_field') ? get_field('hero-news', $post_id) : [];
    $hero = is_array($hero) && !array_is_list($hero) ? $hero : [];

    $title = is_scalar($hero['title'] ?? null) || ($hero['title'] ?? null) instanceof Stringable
        ? trim((string) $hero['title'])
        : '';
    if ($title === '') {
        $title = trim((string) get_the_title($post_id));
    }

    $images = is_array($hero['images'] ?? null) && !array_is_list($hero['images'])
        ? $hero['images']
        : [];

    return [
        'post_id' => $post_id,
        'date' => $hero['date'] ?? '',
        'title' => $title,
        'image' => $images['desktop'] ?? null,
        'link' => [
            'url' => get_permalink($post_id),
            'title' => $title,
        ],
    ];
}, $items);
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
