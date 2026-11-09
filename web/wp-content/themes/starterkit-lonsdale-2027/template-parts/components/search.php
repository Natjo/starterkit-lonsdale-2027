<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'value' => '',
    'action' => '',
    'name' => 's',
    'label' => 'Rechercher',
    'placeholder' => 'Rechercher…',
    'submit_label' => 'Rechercher',
    'classes' => '',
    'attributes' => [],
    'hidden_fields' => [],
    'autocomplete' => '',
]);
$action = esc_url(theme_string($args['action']));
$action = $action !== '' ? $action : esc_url(home_url('/'));
$requested_name = theme_string($args['name']);
$name = preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $requested_name) ? $requested_name : 's';
$label = theme_string($args['label']) ?: 'Rechercher';
$placeholder = theme_string($args['placeholder']);
$submit_label = theme_string($args['submit_label']) ?: 'Rechercher';
$autocomplete = theme_string($args['autocomplete']);
$field_id = component_id('', 'search-field-');
$classes = component_classes('search-form', $args['classes']);
$hidden_fields = array_values(array_filter(component_list($args['hidden_fields']), static function ($field) use ($name): bool {
    return is_array($field)
        && preg_match('/^[A-Za-z][A-Za-z0-9_.\-\[\]]*$/', theme_string($field['name'] ?? '')) === 1
        && theme_string($field['name']) !== $name
        && (is_scalar($field['value'] ?? null) || ($field['value'] ?? null) instanceof Stringable);
}));
$attributes = component_attributes($args['attributes'], ['class', 'action', 'method', 'role']);
?>

<form class="<?= $classes ?>" action="<?= $action ?>" method="get" role="search"<?= $attributes ?>>
    <?php foreach ($hidden_fields as $field) : ?>
        <input type="hidden" name="<?= esc_attr(theme_string($field['name'])) ?>" value="<?= esc_attr(theme_string($field['value'])) ?>">
    <?php endforeach; ?>
    <label class="sr-only" for="<?= esc_attr($field_id) ?>"><?= esc_html($label) ?></label>
    <div class="search-field">
        <input id="<?= esc_attr($field_id) ?>" type="search" name="<?= esc_attr($name) ?>" value="<?= esc_attr(theme_string($args['value'])) ?>"<?= $placeholder !== '' ? ' placeholder="' . esc_attr($placeholder) . '"' : '' ?>>
        <button class="search-submit" type="submit"><?= esc_html($submit_label) ?></button>
    </div>
</form>
