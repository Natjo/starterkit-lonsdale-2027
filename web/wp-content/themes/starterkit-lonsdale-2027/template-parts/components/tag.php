<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$name = $params['name'] ?? '';
if (!is_scalar($name) && !$name instanceof Stringable) {
    return;
}

$name = trim((string) $name);
if ($name === '') {
    return;
}

$classes = component_classes('tag', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<span class="<?= $classes ?>"<?= $attributes ?>><?= esc_html($name) ?></span>
