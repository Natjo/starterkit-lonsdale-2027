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

$label = $params['label'] ?? '';
$label = is_scalar($label) || $label instanceof Stringable ? trim((string) $label) : '';
$classes = component_classes('picto', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<span class="<?= $classes ?>"<?= $attributes ?>>
    <?php component('icon', [
        'name' => $name,
        'label' => $label,
    ]); ?>
</span>
