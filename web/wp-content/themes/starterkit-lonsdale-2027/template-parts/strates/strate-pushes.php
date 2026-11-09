<?php
$args = get_template_args();


$selected = is_array($args['items'] ?? null) ? $args['items'] : [];
theme_prime_posts($selected);
$items = [];
foreach (theme_collect_post_ids($selected) as $post_id) {
    $post_type = get_post_type($post_id);
    if (!is_string($post_type)) continue;

    $configured_card = apply_filters('theme_content_type_card', $post_type, $post_type);
    $card_name = is_scalar($configured_card) || $configured_card instanceof Stringable
        ? sanitize_key(trim((string) $configured_card))
        : '';
    $card_name = resolve_card_name($card_name);
    $item = $card_name !== '' ? theme_post_card_item($post_id) : null;
    if ($item !== null) $items[] = ['card' => $card_name, 'data' => $item];
}

$title = $args['title'] ?? '';
$text = $args['text'] ?? '';
$link = is_array($args['link'] ?? null) ? $args['link'] : [];
$has_header = theme_string($title) !== ''
    || theme_string($text) !== ''
    || (theme_string($link['url'] ?? '') !== '' && theme_string($link['title'] ?? '') !== '');

if (!$has_header && $items === []) return;
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

        <?php if ($items !== []) : ?>
            <ul class="list strate-list" role="list">
                <?php foreach ($items as $item) : ?>
                    <li class="item"><?php card($item['card'], $item['data']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
