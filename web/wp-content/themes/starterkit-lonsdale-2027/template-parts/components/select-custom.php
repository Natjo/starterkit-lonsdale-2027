<?php
/** @var array<string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if ($params === [] || array_is_list($params)) return;

$has_value = array_key_exists('value', $params) && $params['value'] !== null;
$args = normalize_args($params, [
    'options' => [],
    'name' => '',
    'id' => '',
    'label' => '',
    'aria_label' => '',
    'value' => null,
    'placeholder' => 'Sélectionner',
    'multiple' => false,
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

$multiple = $to_bool($args['multiple']);
$required = $to_bool($args['required']);
$disabled = $to_bool($args['disabled']);
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

$requested_values = [];
if ($has_value) {
    $values = $multiple && is_array($args['value']) ? $args['value'] : [$args['value']];
    foreach ($values as $value) {
        $value = $to_string($value);
        if ($value !== '') $requested_values[] = $value;
    }
    $requested_values = array_values(array_unique($requested_values));
}

$selected_index = null;
if ($multiple) {
    foreach ($normalized_options as &$option) {
        $option['selected'] = !$option['disabled'] && ($has_value
            ? in_array($option['value'], $requested_values, true)
            : $option['selected']);
    }
    unset($option);
} else {
    $requested_value = $requested_values[0] ?? '';
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

    foreach ($normalized_options as $index => &$option) {
        $option['selected'] = $index === $selected_index;
    }
    unset($option);
}

$placeholder = $to_string($args['placeholder']) ?: 'Sélectionner';
$selected_labels = array_map(
    static fn(array $option): string => $option['label'],
    array_filter($normalized_options, static fn(array $option): bool => $option['selected'])
);
$button_label = $selected_labels !== [] ? implode(', ', $selected_labels) : $placeholder;
$requested_id = sanitize_html_class($to_string($args['id']));
$base_id = $requested_id !== ''
    ? $requested_id
    : (function_exists('wp_unique_id') ? wp_unique_id('select-custom-') : uniqid('select-custom-', false));
$label_id = $base_id . '-label';
$button_id = $base_id . '-button';
$list_id = $base_id . '-list';
$label = $to_string($args['label']);
$accessible_label = $label !== '' ? $label : ($to_string($args['aria_label']) ?: $name);
$submitted_name = $multiple && !str_ends_with($name, '[]') ? $name . '[]' : $name;
$classes = component_classes('select-custom', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'data-module', 'data-context', 'data-placeholder']);
$select_attributes = component_attributes($args['select_attributes'], [
    'id',
    'name',
    'class',
    'multiple',
    'required',
    'disabled',
    'aria-label',
    'aria-labelledby',
]);
$native_option_index = $multiple ? 0 : 1;
?>

<div class="<?= $classes ?>" data-module="components/select-custom" data-context="@visible true" data-placeholder="<?= esc_attr($placeholder) ?>"<?= $attributes ?>>
    <label id="<?= esc_attr($label_id) ?>" class="select-custom-label<?= $label === '' ? ' select-custom-label--visually-hidden' : '' ?>" for="<?= esc_attr($base_id) ?>"><?= esc_html($accessible_label) ?></label>
    <select id="<?= esc_attr($base_id) ?>" class="select-custom-native" name="<?= esc_attr($submitted_name) ?>" data-select-native<?= $multiple ? ' multiple' : '' ?><?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $select_attributes ?>>
        <?php if (!$multiple) : ?>
            <option value=""<?= $selected_index === null ? ' selected' : '' ?><?= $required ? ' disabled' : '' ?>><?= esc_html($placeholder) ?></option>
        <?php endif; ?>
        <?php foreach ($normalized_options as $option) : ?>
            <option value="<?= esc_attr($option['value']) ?>"<?= $option['selected'] ? ' selected' : '' ?><?= $option['disabled'] ? ' disabled' : '' ?>><?= esc_html($option['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="select-custom-ui" data-select-ui hidden>
        <button
            id="<?= esc_attr($button_id) ?>"
            type="button"
            role="combobox"
            aria-controls="<?= esc_attr($list_id) ?>"
            aria-expanded="false"
            aria-haspopup="listbox"
            aria-labelledby="<?= esc_attr($label_id . ' ' . $button_id) ?>"
            <?= $required ? 'aria-required="true" ' : '' ?>
            <?= $disabled ? 'disabled ' : '' ?>
            data-select-button
        ><span data-select-button-label><?= esc_html($button_label) ?></span></button>
        <ul id="<?= esc_attr($list_id) ?>" role="listbox"<?= $multiple ? ' aria-multiselectable="true"' : '' ?> data-select-list hidden>
            <?php foreach ($normalized_options as $index => $option) : ?>
                <li
                    id="<?= esc_attr($base_id . '-option-' . $index) ?>"
                    role="option"
                    data-select-option
                    data-index="<?= esc_attr($index + $native_option_index) ?>"
                    aria-selected="<?= $option['selected'] ? 'true' : 'false' ?>"
                    <?= $option['disabled'] ? 'aria-disabled="true"' : '' ?>
                ><?= esc_html($option['label']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
