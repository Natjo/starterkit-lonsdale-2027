<?php
/** @var array<string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

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

$name = component_string($args['name']);
if ($name === '') return;

$multiple = component_bool($args['multiple']);
$required = component_bool($args['required']);
$disabled = component_bool($args['disabled']);
$normalized_options = component_options($args['options']);
if ($normalized_options === []) return;

$requested_values = [];
if ($has_value) {
    $values = $multiple && is_array($args['value']) ? $args['value'] : [$args['value']];
    foreach ($values as $value) {
        $value = component_string($value);
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

    $placeholder = component_string($args['placeholder']);
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

$placeholder = component_string($args['placeholder']) ?: 'Sélectionner';
$selected_labels = array_map(
    static fn(array $option): string => $option['label'],
    array_filter($normalized_options, static fn(array $option): bool => $option['selected'])
);
$button_label = $selected_labels !== [] ? implode(', ', $selected_labels) : $placeholder;
$base_id = component_id($args['id'], 'select-custom-');
$label_id = $base_id . '-label';
$button_id = $base_id . '-button';
$list_id = $base_id . '-list';
$label = component_string($args['label']);
$accessible_label = $label !== '' ? $label : (component_string($args['aria_label']) ?: $name);
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
