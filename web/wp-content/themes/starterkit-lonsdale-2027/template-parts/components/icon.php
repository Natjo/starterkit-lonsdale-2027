<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$name = $params['name'] ?? '';
if (!is_scalar($name) && !$name instanceof Stringable) {
    return;
}

$name = sanitize_html_class(trim((string) $name));
if ($name === '') {
    return;
}

$width = is_numeric($params['width'] ?? null) ? max(1, (int) $params['width']) : 24;
$height = is_numeric($params['height'] ?? null) ? max(1, (int) $params['height']) : 24;
$classes = component_classes('icon', 'icon-' . $name, $params['classes'] ?? '');

$label = $params['label'] ?? '';
$label = is_scalar($label) || $label instanceof Stringable ? trim((string) $label) : '';
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'width',
    'height',
    'role',
    'aria-label',
    'aria-hidden',
    'focusable',
]);
$sprite_url = rtrim(THEME_ASSETS, '/') . '/img/icons.svg#' . $name;
?>
<svg class="<?= $classes ?>" width="<?= esc_attr($width) ?>" height="<?= esc_attr($height) ?>"<?= $label !== '' ? ' role="img" aria-label="' . esc_attr($label) . '"' : ' aria-hidden="true"' ?> focusable="false"<?= $attributes ?>>
    <use href="<?= esc_url($sprite_url) ?>"></use>
</svg>
