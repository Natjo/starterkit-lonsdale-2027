<?php
$args = get_template_args();

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$post_type = sanitize_key($to_string($args['post_type'] ?? ''));
$configured_card = $to_string($args['card'] ?? '')
    ?: apply_filters('theme_content_type_card', $post_type, $post_type);
$card = sanitize_key($to_string($configured_card));
$resolved_card = resolve_card_name($card);
$can_render_items = $post_type !== ''
    && post_type_exists($post_type)
    && $resolved_card !== '';

$mode = $to_string($args['mode'] ?? 'manual');
$mode = in_array($mode, ['manual', 'automatic'], true) ? $mode : 'manual';
$limit = is_numeric($args['limit'] ?? null) ? (int) $args['limit'] : 3;
$limit = max(1, min(12, $limit));
$ids = [];

if ($can_render_items && $mode === 'automatic') {
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'ignore_sticky_posts' => true,
        'suppress_filters' => false,
    ]);
    foreach ($posts as $post) {
        $id = is_object($post)
            ? (isset($post->ID) ? (int) $post->ID : 0)
            : filter_var($post, FILTER_VALIDATE_INT);
        if ($id > 0) $ids[$id] = $id;
    }
    theme_prime_posts(array_values($ids));
} elseif ($can_render_items) {
    $selected = is_array($args['items'] ?? null) ? $args['items'] : [];
    theme_prime_posts($selected);

    foreach (theme_collect_post_ids($selected) as $id) {
        if (get_post_type($id) === $post_type) $ids[$id] = $id;
    }
}

$items = array_values(array_filter(array_map('theme_post_card_item', array_values($ids))));

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
    <div class="strate-inner">
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
    </div>
</section>
