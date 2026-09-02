<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$name = $params['name'] ?? '';
if (!is_scalar($name) && !$name instanceof Stringable) {
    return;
}

$name = trim((string) $name);
if ($name === '') {
    return;
}

$classes = component_classes('badge', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<span class="<?= $classes ?>"<?= $attributes ?>><?= esc_html($name) ?></span>
