<?php
$args = get_template_args();

$post_type = sanitize_key(theme_string($args['post_type'] ?? ''));
$configured_card = theme_string($args['card'] ?? '')
    ?: apply_filters('theme_content_type_card', $post_type, $post_type);
$card = sanitize_key(theme_string($configured_card));
$resolved_card = resolve_card_name($card);
$can_render_items = $post_type !== ''
    && post_type_exists($post_type)
    && $resolved_card !== '';

$limit = is_numeric($args['limit'] ?? null) ? (int) $args['limit'] : 3;
$limit = max(1, min(12, $limit));
$items = $can_render_items ? theme_pushes_items($post_type, $limit)['items'] : [];

$title = $args['title'] ?? '';
$text = $args['text'] ?? '';
$link = is_array($args['link'] ?? null) ? $args['link'] : [];
$has_header = theme_string($title) !== ''
    || theme_string($text) !== ''
    || (theme_string($link['url'] ?? '') !== '' && theme_string($link['title'] ?? '') !== '');

if (!$has_header && $items === []) {
    return;
}
?>

<section <?= options('strate strate-pushes-auto', $args) ?>>
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
