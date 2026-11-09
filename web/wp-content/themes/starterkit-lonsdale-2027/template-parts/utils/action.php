<?php
/**
 * Rendu interne partagé par btn et link.
 * @var array<int|string, mixed>|null $params
 * @var string $action_class
 */

$params = component_args($params ?? null);
if ($params === null) return;

$is_link = array_key_exists('link', $params);
$is_span = strtolower(theme_string($params['element'] ?? '')) === 'span';
$element = $is_span ? 'span' : ($is_link ? 'a' : 'button');
$attributes_input = is_array($params['attributes'] ?? null) ? $params['attributes'] : [];
$href = $target = $rel = $type = '';

if ($is_link) {
    $link = theme_link($params['link'], $attributes_input);
    if ($link['url'] === '' || $link['title'] === '') return;

    $label = $link['title'];
    $attributes_input = $link['attributes'];
    if ($element === 'a') {
        $href = !$link['disabled'] ? ' href="' . esc_url($link['url']) . '"' : '';
        $target = !$link['disabled'] && $link['target'] !== '' ? ' target="' . esc_attr($link['target']) . '"' : '';
        $rel = $link['rel'] !== '' ? ' rel="' . esc_attr($link['rel']) . '"' : '';
    }
} else {
    $label = theme_string($params['name'] ?? '');
    if ($label === '') return;

    if ($element === 'button') {
        $button_type = 'button';
        foreach ($attributes_input as $attribute_name => $value) {
            if (strtolower((string) $attribute_name) !== 'type') continue;
            $candidate = strtolower(theme_string($value));
            $button_type = in_array($candidate, ['button', 'submit', 'reset'], true) ? $candidate : 'button';
        }
        $type = ' type="' . esc_attr($button_type) . '"';
    }
}

$reserved_attributes = ['class', 'href', 'target', 'rel', 'type'];
if ($is_span) {
    $reserved_attributes = array_merge($reserved_attributes, [
        'disabled', 'form', 'formaction', 'formenctype', 'formmethod',
        'formnovalidate', 'formtarget', 'name', 'value',
    ]);
}
$attributes = component_attributes($attributes_input, $reserved_attributes);
$classes = component_classes($action_class, $params['classes'] ?? '');
$svg = component_bool($params['svg'] ?? false);
$icon = component_args($params['icon'] ?? null);
if ($icon !== null) {
    $icon = array_replace(['width' => 16, 'height' => 16], $icon);
}
?>
<<?= $element ?><?= $type . $href ?> class="<?= $classes ?>"<?= $attributes . $target . $rel ?>>
    <?php if (!$svg && $icon === null) : ?>
        <?= esc_html($label) ?>
    <?php else : ?>
        <span><?= esc_html($label) ?></span>
    <?php endif; ?>
    <?php if ($svg) : ?>
        <svg class="<?= esc_attr($action_class) ?>-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    <?php elseif ($icon !== null) : ?>
        <?php component('icon', $icon); ?>
    <?php endif; ?>
</<?= $element ?>>
