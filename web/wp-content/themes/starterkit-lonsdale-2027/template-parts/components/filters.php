<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'action' => '',
    'items' => [],
    'submit_label' => 'Filtrer',
    'reset_label' => 'Réinitialiser',
    'reset_url' => '',
    'show_reset' => false,
    'result_count' => null,
    'result_count_label' => '',
    'aria_label' => '',
    'ajax' => false,
    'ajax_group' => '',
    'ajax_target' => '',
    'ajax_submit_on_change' => false,
    'classes' => '',
    'attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';
$to_bool = static fn(mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOL);
$valid_name = static fn(string $name): bool => preg_match('/^[A-Za-z][A-Za-z0-9_.\-\[\]]*$/', $name) === 1;
$items = is_array($args['items']) && array_is_list($args['items']) ? $args['items'] : [];
$items = array_values(array_filter($items, static function ($item) use ($to_string, $valid_name): bool {
    if (!is_array($item) || array_is_list($item)) return false;

    return $valid_name($to_string($item['name'] ?? ''));
}));
if ($items === []) return;

$action = esc_url($to_string($args['action']));
$action = $action !== '' ? $action : esc_url(home_url('/'));
$reset_url = esc_url($to_string($args['reset_url']));
$submit_label = $to_string($args['submit_label']) ?: 'Filtrer';
$reset_label = $to_string($args['reset_label']) ?: 'Réinitialiser';
$aria_label = $to_string($args['aria_label']);
$ajax = $to_bool($args['ajax']);
$ajax_group = sanitize_html_class($to_string($args['ajax_group']));
$ajax_target = sanitize_html_class($to_string($args['ajax_target']));
$ajax = $ajax && $ajax_group !== '' && $ajax_target !== '';
$classes = component_classes('filters', $args['classes']);
$attributes = component_attributes($args['attributes'], [
    'class', 'action', 'method', 'aria-label', 'data-module', 'data-filters-group',
    'data-filters-target', 'data-filters-submit-on-change',
]);
$result_count = is_numeric($args['result_count']) ? max(0, (int) $args['result_count']) : null;
$result_count_label = $to_string($args['result_count_label']);
$control_types = ['text', 'search', 'select', 'checkbox', 'checkboxes', 'radios', 'number', 'date'];
?>

<form class="<?= $classes ?>" action="<?= $action ?>" method="get"<?= $aria_label !== '' ? ' aria-label="' . esc_attr($aria_label) . '"' : '' ?><?= $ajax ? ' data-module="components/filters" data-filters-group="' . esc_attr($ajax_group) . '" data-filters-target="' . esc_attr($ajax_target) . '" data-filters-submit-on-change="' . ($to_bool($args['ajax_submit_on_change']) ? 'true' : 'false') . '"' : '' ?><?= $attributes ?>>
    <div class="filters-fields">
        <?php foreach ($items as $item) :
            $type = strtolower($to_string($item['type'] ?? 'text'));
            $name = $to_string($item['name'] ?? '');
            $control = strtolower($to_string($item['control'] ?? ''));
            $value = $item['value'] ?? '';
            $options = is_array($item['options'] ?? null) ? $item['options'] : [];

            if (in_array($type, ['taxonomy', 'tags', 'relation'], true)) {
                $type = in_array($control, ['select', 'radios', 'checkboxes'], true) ? $control : 'checkboxes';
            } elseif (in_array($type, ['acf', 'location', 'status', 'post_type', 'sort'], true)) {
                $allowed_alias_controls = [
                    'text', 'search', 'select', 'checkbox', 'checkboxes', 'radios',
                    'number', 'date', 'date_range', 'number_range',
                ];
                $type = in_array($control, $allowed_alias_controls, true) ? $control : 'select';
            } elseif (in_array($type, ['boolean', 'featured'], true)) {
                $type = 'checkbox';
            }

            if (in_array($type, ['checkboxes', 'radios'], true)) {
                $selected_values = is_array($value) ? $value : [$value];
                $selected_values = array_map($to_string, $selected_values);
                $options = array_map(static function ($option) use ($selected_values, $to_string): array {
                    $option = is_array($option) ? $option : [];
                    $option_value = $to_string($option['value'] ?? '');
                    $option['checked'] = in_array($option_value, $selected_values, true);
                    return $option;
                }, $options);
            }

            if ($type === 'hidden') : ?>
                <input type="hidden" name="<?= esc_attr($name) ?>" value="<?= esc_attr($to_string($value)) ?>">
            <?php elseif (in_array($type, ['date_range', 'number_range'], true)) :
                $range_type = $type === 'date_range' ? 'date' : 'number';
                $range_value = is_array($value) && !array_is_list($value) ? $value : [];
                $from_key = $type === 'date_range' ? 'from' : 'min';
                $to_key = $type === 'date_range' ? 'to' : 'max';
                $from_label = $to_string($item['from_label'] ?? '') ?: ($type === 'date_range' ? 'Du' : 'Minimum');
                $to_label = $to_string($item['to_label'] ?? '') ?: ($type === 'date_range' ? 'Au' : 'Maximum');
                ?>
                <fieldset class="filters-range filters-range-<?= esc_attr($range_type) ?>">
                    <?php if ($to_string($item['label'] ?? '') !== '') : ?>
                        <legend><?= esc_html($to_string($item['label'])) ?></legend>
                    <?php endif; ?>
                    <?php component('field', [
                        'type' => $range_type,
                        'name' => $name . '[' . $from_key . ']',
                        'label' => $from_label,
                        'value' => $range_value[$from_key] ?? '',
                        'min' => $item['min'] ?? null,
                        'max' => $item['max'] ?? null,
                    ]); ?>
                    <?php component('field', [
                        'type' => $range_type,
                        'name' => $name . '[' . $to_key . ']',
                        'label' => $to_label,
                        'value' => $range_value[$to_key] ?? '',
                        'min' => $item['min'] ?? null,
                        'max' => $item['max'] ?? null,
                    ]); ?>
                </fieldset>
            <?php elseif (in_array($type, $control_types, true)) : ?>
                <?php component('field', [
                    'type' => $type,
                    'name' => $name,
                    'label' => $item['label'] ?? '',
                    'value' => $value,
                    'options' => $options,
                    'placeholder' => $item['placeholder'] ?? '',
                    'autocomplete' => $item['autocomplete'] ?? '',
                    'checked' => $item['checked'] ?? $value,
                    'min' => $item['min'] ?? null,
                    'max' => $item['max'] ?? null,
                    'classes' => $item['classes'] ?? '',
                    'attributes' => $item['attributes'] ?? [],
                ]); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($result_count !== null) : ?>
        <p class="filters-count" aria-live="polite">
            <strong><?= esc_html((string) $result_count) ?></strong>
            <?php if ($result_count_label !== '') : ?> <?= esc_html($result_count_label) ?><?php endif; ?>
        </p>
    <?php endif; ?>

    <div class="filters-actions">
        <?php component('btn', [
            'name' => $submit_label,
            'classes' => 'btn-1 filters-submit',
            'attributes' => ['type' => 'submit'],
        ]); ?>

        <?php if ($to_bool($args['show_reset']) && $reset_url !== '') : ?>
            <?php component('btn', [
                'link' => [
                    'url' => $reset_url,
                    'title' => $reset_label,
                ],
                'classes' => 'btn-2 filters-reset',
            ]); ?>
        <?php endif; ?>
    </div>
</form>
