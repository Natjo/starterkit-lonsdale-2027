<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, ['text' => '', 'classes' => '', 'attributes' => []]);
$text = is_scalar($args['text']) || $args['text'] instanceof Stringable ? trim((string) $args['text']) : '';

if ($text === '') return;

$classes = component_classes('intro', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<div class="<?= $classes ?>"<?= $attributes ?>><?= wp_kses_post($text) ?></div>
