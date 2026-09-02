<?php
$args = get_template_args();

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$post_type = sanitize_key($to_string($args['post_type'] ?? ''));
$card = sanitize_key($to_string($args['card'] ?? $post_type));
$resolved_card = resolve_card_name($card);
$can_render_items = $post_type !== ''
    && post_type_exists($post_type)
    && $resolved_card !== '';

$mode = $to_string($args['mode'] ?? 'manual');
$mode = in_array($mode, ['manual', 'automatic'], true) ? $mode : 'manual';
$limit = is_numeric($args['limit'] ?? null) ? (int) $args['limit'] : 3;
$limit = max(1, min(12, $limit));
$items = [];

if ($can_render_items && $mode === 'automatic') {
    $items = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'fields' => 'ids',
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
    ]);
} elseif ($can_render_items) {
    $selected = is_array($args['items'] ?? null) ? $args['items'] : [];
    foreach ($selected as $item) {
        $id = filter_var($item, FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0 && get_post_type($id) === $post_type) {
            $items[] = $id;
        }
    }
}

$items = array_values(array_unique(array_filter(array_map('intval', $items))));
if ($items !== [] && function_exists('update_meta_cache')) {
    update_meta_cache('post', $items);
}

if ($post_type === 'news' && $resolved_card === 'card-news' && function_exists('get_field')) {
    $items = array_map(static function (int $post_id): array {
        $hero = get_field('hero-news', $post_id);
        $hero = is_array($hero) && !array_is_list($hero) ? $hero : [];
        $images = is_array($hero['images'] ?? null) && !array_is_list($hero['images'])
            ? $hero['images']
            : [];

        $title = is_scalar($hero['title'] ?? null) || ($hero['title'] ?? null) instanceof Stringable
            ? trim((string) $hero['title'])
            : '';
        if ($title === '') {
            $title = trim((string) get_the_title($post_id));
        }

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
}

$title = $args['title'] ?? '';
$text = $args['text'] ?? '';
$link = is_array($args['link'] ?? null) ? $args['link'] : [];
$has_header = $to_string($title) !== ''
    || $to_string($text) !== ''
    || ($to_string($link['url'] ?? '') !== '' && $to_string($link['title'] ?? '') !== '');

if (!$has_header && $items === []) {
    return;
}
?>

<section <?= options('strate strate-pushes', $args) ?>>
    <?php component('header', [
        'title' => $title,
        'title_classes' => 'title-2 strate-title',
        'text' => $text,
        'text_classes' => 'rte strate-text',
        'link' => $link,
        'classes' => 'strate-header',
    ]); ?>

    <?php component('list', [
        'items' => $items,
        'card' => $card,
        'classes' => 'strate-list',
    ]); ?>
</section>
