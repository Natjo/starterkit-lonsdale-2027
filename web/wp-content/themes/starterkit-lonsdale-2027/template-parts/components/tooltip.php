<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$label = $to_string($params['label'] ?? '');
$content = $to_string($params['content'] ?? '');

if ($label === '' || $content === '') {
    return;
}

$classes = component_classes('tooltip', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'data-tooltip',
    'data-module',
    'data-tooltip-open',
    'data-tooltip-dismissed',
]);
$uid = function_exists('wp_unique_id') ? wp_unique_id('tooltip-') : uniqid('tooltip-');
$allowed_content = [
    'br' => [],
    'strong' => [],
    'em' => [],
    'b' => [],
    'i' => [],
    'small' => [],
    'sup' => [],
    'sub' => [],
    'span' => ['class' => true],
];
?>

<span
    class="<?= $classes ?>"
    data-tooltip
    data-module="components/tooltip"<?= $attributes ?>>
    <button
        class="trigger-button"
        type="button"
        aria-describedby="<?= esc_attr($uid) ?>">
        <?= esc_html($label) ?>
    </button>

    <span
        class="tooltip-content"
        id="<?= esc_attr($uid) ?>"
        role="tooltip">
        <?= wp_kses($content, $allowed_content) ?>
    </span>
</span>
