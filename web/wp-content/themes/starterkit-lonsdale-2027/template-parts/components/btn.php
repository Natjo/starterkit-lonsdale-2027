<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$to_string = static fn (mixed $value): string =>
    is_scalar($value) || $value instanceof Stringable ? trim((string) $value) : '';

$classes = component_classes('btn', $params['classes'] ?? '');
$icon = is_array($params['icon'] ?? null)
    && !array_is_list($params['icon'])
    && $params['icon'] !== []
        ? $params['icon']
        : null;
$attributes_input = is_array($params['attributes'] ?? null) ? $params['attributes'] : [];

if (array_key_exists('link', $params)) {
    if (!is_array($params['link'])) {
        return;
    }

    $link = normalize_action_link($params['link'], $attributes_input);
    if ($link['url'] === '' || $link['title'] === '') {
        return;
    }

    $element = strtolower($to_string($params['element'] ?? 'a')) === 'span' ? 'span' : 'a';
    $link_attributes = $link['attributes'];

    if ($element === 'span') {
        $span_reserved_attributes = [
            'disabled',
            'form',
            'formaction',
            'formmethod',
            'formtarget',
            'name',
            'value',
        ];

        foreach (array_keys($link_attributes) as $attribute_name) {
            if (in_array(strtolower((string) $attribute_name), $span_reserved_attributes, true)) {
                unset($link_attributes[$attribute_name]);
            }
        }
    }

    $attributes = component_attributes($link_attributes, ['class', 'href', 'target', 'rel', 'type']);
    $href = $element === 'a' && !$link['disabled'] ? ' href="' . $link['url'] . '"' : '';
    $target = $element === 'a' && !$link['disabled'] && $link['target'] !== '' ? ' target="' . esc_attr($link['target']) . '"' : '';
    $rel = $element === 'a' && $link['rel'] !== '' ? ' rel="' . esc_attr($link['rel']) . '"' : '';
    ?>
    <<?= $element ?><?= $href ?> class="<?= $classes ?>"<?= $attributes . $target . $rel ?>><span><?= esc_html($link['title']) ?></span><?php if ($icon !== null) component('icon', $icon); ?></<?= $element ?>>
    <?php
    return;
}

$name = $to_string($params['name'] ?? '');
if ($name === '') {
    return;
}

$reserved_attributes = ['class', 'href', 'target', 'rel', 'type'];

$type = 'button';
foreach (array_keys($attributes_input) as $attribute_name) {
    $normalized_name = strtolower((string) $attribute_name);
    if ($normalized_name === 'type') {
        $candidate = strtolower($to_string($attributes_input[$attribute_name]));
        $type = in_array($candidate, ['button', 'submit', 'reset'], true) ? $candidate : 'button';
        unset($attributes_input[$attribute_name]);
    } elseif (in_array($normalized_name, ['class', 'href', 'target', 'rel'], true)) {
        unset($attributes_input[$attribute_name]);
    }
}

$attributes = component_attributes($attributes_input, $reserved_attributes);
?>
<button type="<?= esc_attr($type) ?>" class="<?= $classes ?>"<?= $attributes ?>><span><?= esc_html($name) ?></span><?php if ($icon !== null) component('icon', $icon); ?></button>
