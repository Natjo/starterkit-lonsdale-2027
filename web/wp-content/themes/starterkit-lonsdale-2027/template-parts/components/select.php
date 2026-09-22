<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

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

$name = component_string($args['name']);
$normalized_options = component_options($args['options']);
if ($name === '') return;
if ($normalized_options === []) return;

$requested_value = is_scalar($args['value']) || $args['value'] instanceof Stringable
    ? component_string($args['value'])
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

$placeholder = component_string($args['placeholder']);
if ($selected_index === null && $placeholder === '') {
    foreach ($normalized_options as $index => $option) {
        if (!$option['disabled']) {
            $selected_index = $index;
            break;
        }
    }
}

$id = component_id($args['id'], 'select-field-');
$label = component_string($args['label']);
$aria_label = component_string($args['aria_label']) ?: $name;
$required = component_bool($args['required']);
$disabled = component_bool($args['disabled']);
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
