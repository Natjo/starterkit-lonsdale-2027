<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) {
    return;
}

$args = normalize_args($params, [
    'value' => '',
    'action' => '',
    'name' => 's',
    'label' => 'Rechercher',
    'placeholder' => 'Rechercher…',
    'submit_label' => 'Rechercher',
    'classes' => '',
    'attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$action = esc_url($to_string($args['action']));
$action = $action !== '' ? $action : esc_url(home_url('/'));
$requested_name = $to_string($args['name']);
$name = preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $requested_name) ? $requested_name : 's';
$label = $to_string($args['label']) ?: 'Rechercher';
$placeholder = $to_string($args['placeholder']);
$submit_label = $to_string($args['submit_label']) ?: 'Rechercher';
$field_id = function_exists('wp_unique_id') ? wp_unique_id('search-field-') : uniqid('search-field-', false);
$classes = component_classes('search-form', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'action', 'method', 'role']);
?>

<form class="<?= $classes ?>" action="<?= $action ?>" method="get" role="search"<?= $attributes ?>>
    <label class="sr-only" for="<?= esc_attr($field_id) ?>"><?= esc_html($label) ?></label>
    <div class="search-field">
        <input id="<?= esc_attr($field_id) ?>" type="search" name="<?= esc_attr($name) ?>" value="<?= esc_attr($to_string($args['value'])) ?>"<?= $placeholder !== '' ? ' placeholder="' . esc_attr($placeholder) . '"' : '' ?>>
        <button class="search-submit" type="submit"><?= esc_html($submit_label) ?></button>
    </div>
</form>
