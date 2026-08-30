<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params) || !is_array($params['link'] ?? null)) {
    return;
}

$link = normalize_action_link($params['link'], $params['attributes'] ?? []);
if ($link['url'] === '' || $link['title'] === '') {
    return;
}

$classes = component_classes('link', $params['classes'] ?? '');
$attributes = component_attributes($link['attributes'], ['class', 'href', 'target', 'rel']);
$icon = is_array($params['icon'] ?? null)
    && !array_is_list($params['icon'])
    && $params['icon'] !== []
        ? $params['icon']
        : null;
$href = $link['disabled'] ? '' : ' href="' . $link['url'] . '"';
$target = !$link['disabled'] && $link['target'] !== '' ? ' target="' . esc_attr($link['target']) . '"' : '';
$rel = $link['rel'] !== '' ? ' rel="' . esc_attr($link['rel']) . '"' : '';
?>
<a<?= $href ?> class="<?= $classes ?>"<?= $attributes . $target . $rel ?>><span><?= esc_html($link['title']) ?></span><?php if ($icon !== null) component('icon', $icon); ?></a>
