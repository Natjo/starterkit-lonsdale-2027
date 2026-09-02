<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$has_explicit_aria_label = array_key_exists('aria_label', $params);

$args = normalize_args($params, [
    'trigger' => ['type' => 'btn', 'label' => 'Open dialog', 'classes' => ''],
    'close_label' => 'Close',
    'aria_label' => 'Dialog',
    'classes' => '',
    'attributes' => [],
]);

$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$content_value = $args['content'] ?? null;
$content = is_scalar($content_value) || $content_value instanceof Stringable
    ? (string) $content_value
    : '';

if (trim($content) === '') {
    return;
}

$trigger = is_array($args['trigger']) ? $args['trigger'] : [];
$trigger_type = $to_string($trigger['type'] ?? 'btn');
$trigger_type = strtolower($trigger_type) === 'link' ? 'link' : 'btn';
$trigger_label = $to_string($trigger['label'] ?? '');
$trigger_classes = $trigger['classes'] ?? '';

$title = $to_string($args['title'] ?? '');
$close_label = $to_string($args['close_label']);
$requested_aria_label = $to_string($args['aria_label']);
$aria_label = $has_explicit_aria_label && $requested_aria_label !== ''
    ? $requested_aria_label
    : ($trigger_label !== '' ? $trigger_label : ($requested_aria_label ?: 'Dialog'));
$id = sanitize_html_class($to_string($args['id'] ?? ''));
$id = $id !== ''
    ? $id
    : (function_exists('wp_unique_id') ? wp_unique_id('dialog-') : uniqid('dialog-'));
$title_id = $id . '-title';

$classes = component_classes('dialog', $args['classes']);
$attributes = component_attributes($args['attributes'], [
    'id',
    'class',
    'open',
    'role',
    'data-dialog',
    'data-module',
    'data-dialog-closing',
    'aria-label',
    'aria-labelledby',
    'aria-modal',
]);

$trigger_attributes = [
    'data-dialog-id' => $id,
    'aria-haspopup' => 'dialog',
    'aria-controls' => $id,
    'aria-expanded' => 'false',
];

if ($trigger_label !== '') {
    if ($trigger_type === 'link') {
        component('link', [
            'link' => [
                'title' => $trigger_label,
                'url' => '#' . $id,
            ],
            'classes' => component_classes('dialog-trigger', $trigger_classes),
            'attributes' => ['role' => 'button'] + $trigger_attributes,
        ]);
    } else {
        component('btn', [
            'name' => $trigger_label,
            'classes' => component_classes('dialog-trigger', $trigger_classes),
            'attributes' => $trigger_attributes,
        ]);
    }
}
?>

<dialog
    id="<?= esc_attr($id) ?>"
    class="<?= $classes ?>"
    data-dialog
    data-module="components/dialog"
    <?php if ($title !== '') : ?>aria-labelledby="<?= esc_attr($title_id) ?>"<?php else : ?>aria-label="<?= esc_attr($aria_label) ?>"<?php endif; ?><?= $attributes ?>>
    <div class="dialog-inner">
        <?php if ($title !== '') : ?>
            <h2 id="<?= esc_attr($title_id) ?>" class="dialog-title"><?= esc_html($title) ?></h2>
        <?php endif; ?>

        <div class="dialog-content">
            <?= wp_kses_post($content) ?>
        </div>

        <?php if ($close_label !== '') : ?>
            <form method="dialog" class="dialog-actions">
                <button type="submit" class="dialog-close" value="close" data-dialog-close>
                    <?= esc_html($close_label) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</dialog>
