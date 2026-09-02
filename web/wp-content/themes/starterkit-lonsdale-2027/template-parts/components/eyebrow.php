<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$text = $params['eyebrow'] ?? '';
if (!is_scalar($text) && !$text instanceof Stringable) {
    return;
}

$text = trim((string) $text);
if ($text === '') {
    return;
}

$element = $params['element'] ?? 'div';
$element = is_scalar($element) ? strtolower(trim((string) $element)) : 'div';
$element = in_array($element, ['p', 'span', 'div'], true) ? $element : 'div';

$icon = $params['icon'] ?? '';
$icon = is_scalar($icon) ? sanitize_html_class(trim((string) $icon)) : '';
$icon_width = is_numeric($params['icon_width'] ?? null) ? max(1, (int) $params['icon_width']) : 15;
$icon_height = is_numeric($params['icon_height'] ?? null) ? max(1, (int) $params['icon_height']) : 15;

$classes = component_classes('eyebrow', $icon !== '' ? 'hasicon' : '', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<<?= $element ?> class="<?= $classes ?>"<?= $attributes ?>><?php if ($icon !== '') : ?><?php component('icon', [
    'name' => $icon,
    'width' => $icon_width,
    'height' => $icon_height,
]); ?><?php endif ?><?= wp_kses_post($text) ?></<?= $element ?>>
