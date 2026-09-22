<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$text = $params['text'] ?? '';
if (!is_scalar($text) && !$text instanceof Stringable) {
    return;
}

$text = trim((string) $text);
if ($text === '') {
    return;
}

$classes = component_classes('text', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<div class="<?= $classes ?>"<?= $attributes ?>>
    <?= wp_kses_post($text) ?>
</div>
