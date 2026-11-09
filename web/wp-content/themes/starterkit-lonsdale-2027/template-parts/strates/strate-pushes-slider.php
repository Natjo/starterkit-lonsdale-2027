<?php
$args = get_template_args();

$post_type = sanitize_key(theme_string($args['post_type'] ?? ''));
$configured_card = theme_string($args['card'] ?? '')
    ?: apply_filters('theme_content_type_card', $post_type, $post_type);
$card = resolve_card_name(sanitize_key(theme_string($configured_card)));
$limit = is_numeric($args['limit'] ?? null) ? (int) $args['limit'] : 6;
$limit = max(1, min(12, $limit));
$items = $card !== '' ? theme_pushes_items($post_type, $limit)['items'] : [];

$title = $args['title'] ?? '';
$text = $args['text'] ?? '';
$link = is_array($args['link'] ?? null) ? $args['link'] : [];
$has_header = theme_string($title) !== ''
    || theme_string($text) !== ''
    || (theme_string($link['url'] ?? '') !== '' && theme_string($link['title'] ?? '') !== '');
if (!$has_header && $items === []) return;

$slider_options = is_array($args['slider_options'] ?? null) && !array_is_list($args['slider_options'])
    ? $args['slider_options']
    : [];
$aria_label = theme_string($args['aria_label'] ?? '') ?: trim(strip_tags(theme_string($title)));
$aria_label = $aria_label !== '' ? $aria_label : 'Carrousel des contenus';
?>

<section <?= options('strate strate-pushes-slider', $args) ?>>
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
            <?php component('slider', array_replace($slider_options, [
                'items' => $items,
                'card' => $card,
                'aria_label' => $aria_label,
                'classes' => 'strate-slider',
            ])); ?>
        <?php endif; ?>
    </div>
</section>
