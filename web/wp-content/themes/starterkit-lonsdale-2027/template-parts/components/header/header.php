<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'title' => '',
    'text' => '',
    'link' => [],
    'classes' => '',
    'attributes' => [],
    'title_classes' => '',
    'title_attributes' => [],
    'text_classes' => '',
    'text_attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$title = $to_string($args['title']);
$text = $to_string($args['text']);
$link = is_array($args['link']) ? $args['link'] : [];
$link_attributes = is_array($link['attributes'] ?? null) ? $link['attributes'] : [];

$link_data = normalize_action_link($link, $link_attributes);
$link_is_valid = $link_data['url'] !== '' && $link_data['title'] !== '';

if ($title === '' && $text === '' && !$link_is_valid) {
    return;
}

$classes = component_classes('component-header', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<header class="<?= $classes ?>"<?= $attributes ?>>
    <?php component('title', [
        'title' => $title,
        'hx' => 2,
        'classes' => $args['title_classes'] ?? '',
        'attributes' => $args['title_attributes'] ?? [],
    ]); ?>

    <?php component('text', [
        'text' => $text,
        'classes' => $args['text_classes'] ?? '',
        'attributes' => $args['text_attributes'] ?? [],
    ]); ?>

    <?php component('link', [
        'link' => $link,
        'classes' => $link['classes'] ?? '',
        'icon' => $link['icon'] ?? [],
        'attributes' => $link_attributes,
    ]); ?>
</header>
