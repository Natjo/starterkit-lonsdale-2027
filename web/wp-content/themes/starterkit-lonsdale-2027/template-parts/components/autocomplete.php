<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'options' => [],
    'name' => '',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'classes' => '',
    'attributes' => [],
]);
$name = component_string($args['name']);
$normalized_options = component_options($args['options']);

if ($normalized_options === [] || $name === '') return;

$base_id = component_id('', 'autocomplete-');
$label_id = $base_id . '-label';
$select_id = $base_id . '-select';
$input_id = $base_id . '-input';
$menu_id = $base_id . '-menu';
$status_id = $base_id . '-status';
$requested_value = component_string($args['value']);
$value = '';
$selected_label = '';
foreach ($normalized_options as $option) {
    $option_value = $option['value'];
    $option_label = $option['label'];
    $disabled = $option['disabled'];
    $selected = $option['selected'];

    if (!$disabled && $value === '' && (
        ($requested_value !== '' && $option_value === $requested_value)
        || ($requested_value === '' && $selected)
    )) {
        $value = $option_value;
        $selected_label = $option_label;
    }

}

$label = component_string($args['label']) ?: $name;
$required = component_bool($args['required'] ?? false);
$classes = component_classes('autocomplete-field', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'data-module', 'data-context']);
?>

<div class="<?= $classes ?>" data-module="components/autocomplete" data-context="@visible true"<?= $attributes ?>>
    <label id="<?= esc_attr($label_id) ?>" for="<?= esc_attr($select_id) ?>"><?= esc_html($label) ?></label>
    <select id="<?= esc_attr($select_id) ?>" class="autocomplete-select" name="<?= esc_attr($name) ?>"<?= $required ? ' required' : '' ?>>
        <option value=""></option>
        <?php foreach ($normalized_options as $option) : ?>
            <option value="<?= esc_attr($option['value']) ?>" data-alt="<?= esc_attr($option['alt']) ?>"<?= $option['value'] === $value ? ' selected' : '' ?><?= $option['disabled'] ? ' disabled' : '' ?>><?= esc_html($option['label']) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="autocomplete">
        <input
            id="<?= esc_attr($input_id) ?>"
            type="text"
            value="<?= esc_attr($selected_label) ?>"
            role="combobox"
            autocomplete="new-password"
            aria-autocomplete="list"
            aria-haspopup="listbox"
            aria-controls="<?= esc_attr($menu_id) ?>"
            aria-labelledby="<?= esc_attr($label_id) ?>"
            aria-describedby="<?= esc_attr($status_id) ?>"
            aria-expanded="false"
            <?= component_string($args['placeholder']) !== '' ? 'placeholder="' . esc_attr(component_string($args['placeholder'])) . '"' : '' ?>
        >
        <ul id="<?= esc_attr($menu_id) ?>" class="hidden" role="listbox"></ul>
    </div>
    <div id="<?= esc_attr($status_id) ?>" class="sr-only" role="status" aria-live="polite"></div>
</div>
