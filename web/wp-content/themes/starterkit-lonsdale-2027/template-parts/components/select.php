<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$args = normalize_args($params, [
    'options' => [],
    'name' => '',
    'id' => '',
    'label' => '',
    'aria_label' => '',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'classes' => '',
    'attributes' => [],
    'select_attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$to_bool = static fn(mixed $value, bool $default = false): bool => filter_var(
    $value,
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
) ?? $default;

$name = $to_string($args['name']);
$options = is_array($args['options']) ? $args['options'] : [];
if ($name === '' || $options === []) return;

$normalized_options = [];
foreach ($options as $option_key => $option) {
    if (is_array($option)) {
        $option_value = $to_string($option['value'] ?? '');
        $option_label = $to_string($option['label'] ?? '');
    } else {
        $option_value = $to_string($option_key);
        $option_label = $to_string($option);
        $option = [];
    }
    if ($option_value === '' || $option_label === '') continue;

    $normalized_options[] = [
        'value' => $option_value,
        'label' => $option_label,
        'selected' => $to_bool($option['selected'] ?? false),
        'disabled' => $to_bool($option['disabled'] ?? false),
    ];
}
if ($normalized_options === []) return;

$requested_value = is_scalar($args['value']) || $args['value'] instanceof Stringable
    ? $to_string($args['value'])
    : '';
$selected_index = null;

if ($requested_value !== '') {
    foreach ($normalized_options as $index => $option) {
        if (!$option['disabled'] && $option['value'] === $requested_value) {
            $selected_index = $index;
            break;
        }
    }
}

if ($selected_index === null) {
    foreach ($normalized_options as $index => $option) {
        if (!$option['disabled'] && $option['selected']) {
            $selected_index = $index;
            break;
        }
    }
}

$placeholder = $to_string($args['placeholder']);
if ($selected_index === null && $placeholder === '') {
    foreach ($normalized_options as $index => $option) {
        if (!$option['disabled']) {
            $selected_index = $index;
            break;
        }
    }
}

$requested_id = sanitize_html_class($to_string($args['id']));
$id = $requested_id !== ''
    ? $requested_id
    : (function_exists('wp_unique_id') ? wp_unique_id('select-field-') : uniqid('select-field-', false));
$label = $to_string($args['label']);
$aria_label = $to_string($args['aria_label']) ?: $name;
$required = $to_bool($args['required']);
$disabled = $to_bool($args['disabled']);
$classes = component_classes('select', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
$select_attributes = component_attributes($args['select_attributes'], [
    'id',
    'name',
    'class',
    'required',
    'disabled',
    'multiple',
    'aria-label',
    'aria-labelledby',
]);
?>
<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($label !== '') : ?>
        <label for="<?= esc_attr($id) ?>"><?= esc_html($label) ?></label>
    <?php endif; ?>
    <span class="select-field">
        <select id="<?= esc_attr($id) ?>" name="<?= esc_attr($name) ?>"<?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $label === '' ? ' aria-label="' . esc_attr($aria_label) . '"' : '' ?><?= $select_attributes ?>>
            <?php if ($placeholder !== '') : ?>
                <option value=""<?= $selected_index === null ? ' selected' : '' ?><?= $required ? ' disabled' : '' ?>><?= esc_html($placeholder) ?></option>
            <?php endif; ?>
            <?php foreach ($normalized_options as $index => $option) : ?>
                <option value="<?= esc_attr($option['value']) ?>"<?= $index === $selected_index ? ' selected' : '' ?><?= $option['disabled'] ? ' disabled' : '' ?>><?= esc_html($option['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </span>
</div>
