<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

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
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$to_bool = static fn(mixed $value, bool $default = false): bool => filter_var(
    $value,
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
) ?? $default;
$options = is_array($args['options']) ? $args['options'] : [];
$name = $to_string($args['name']);

if ($options === [] || $name === '') return;

$base_id = function_exists('wp_unique_id') ? wp_unique_id('autocomplete-') : uniqid('autocomplete-', false);
$label_id = $base_id . '-label';
$select_id = $base_id . '-select';
$input_id = $base_id . '-input';
$menu_id = $base_id . '-menu';
$status_id = $base_id . '-status';
$requested_value = $to_string($args['value']);
$value = '';
$selected_label = '';
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
    $disabled = $to_bool($option['disabled'] ?? false);
    $selected = $to_bool($option['selected'] ?? false);

    if (!$disabled && $value === '' && (
        ($requested_value !== '' && $option_value === $requested_value)
        || ($requested_value === '' && $selected)
    )) {
        $value = $option_value;
        $selected_label = $option_label;
    }

    $normalized_options[] = [
        'value' => $option_value,
        'label' => $option_label,
        'alt' => $to_string($option['alt'] ?? ''),
        'disabled' => $disabled,
    ];
}

if ($normalized_options === []) return;

$label = $to_string($args['label']) ?: $name;
$required = $to_bool($args['required'] ?? false);
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
            <?= $to_string($args['placeholder']) !== '' ? 'placeholder="' . esc_attr($to_string($args['placeholder'])) . '"' : '' ?>
        >
        <ul id="<?= esc_attr($menu_id) ?>" class="hidden" role="listbox"></ul>
    </div>
    <div id="<?= esc_attr($status_id) ?>" class="sr-only" role="status" aria-live="polite"></div>
</div>
