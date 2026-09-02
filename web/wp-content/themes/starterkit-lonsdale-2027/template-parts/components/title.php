<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$title = $params['title'] ?? '';
if (!is_scalar($title) && !$title instanceof Stringable) {
    return;
}

$title = (string) $title;
if ($title === '') {
    return;
}

$level = $params['hx'] ?? 2;
$level = is_numeric($level) ? max(1, min(6, (int) $level)) : 2;
$tag = 'h' . $level;

$classes = component_classes('title', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], ['class']);
?>
<<?= $tag ?> class="<?= $classes ?>"<?= $attributes ?>><?= wp_kses_post($title) ?></<?= $tag ?>>
