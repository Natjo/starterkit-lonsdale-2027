<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'languages' => [],
    'label' => 'Choisir la langue',
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

$languages = is_array($args['languages']) && array_is_list($args['languages']) ? $args['languages'] : [];
if ($languages === []) return;

$label = $to_string($args['label']) ?: 'Choisir la langue';
$classes = component_classes('select-lang', $args['classes']);
$attributes = component_attributes($args['attributes'], [
    'class',
    'data-module',
    'data-context',
]);

$items = [];
foreach ($languages as $language) {
    if (!is_array($language)) continue;

    $code = strtolower($to_string($language['code'] ?? ''));
    $code = preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/', $code) ? $code : '';
    $item_label = $to_string($language['label'] ?? '') ?: strtoupper($code);
    $url = $to_string($language['url'] ?? '');

    $url = esc_url($url);
    if ($item_label === '' || $url === '') continue;

    $items[] = [
        'code' => $code,
        'label' => $item_label,
        'url' => $url,
        'current' => $to_bool($language['current'] ?? false),
        'disabled' => $to_bool($language['disabled'] ?? false),
    ];
}

if ($items === []) return;

$current_item = null;
foreach ($items as $item) {
    if ($item["current"]) {
        $current_item = $item;
        break;
    }
}
$current_item ??= $items[0];

$uid = function_exists('wp_unique_id') ? wp_unique_id('select-lang-') : uniqid('select-lang-', false);
?>

<div class="<?= $classes ?>" data-module="components/select-lang" data-context="@visible true"<?= $attributes ?>>
    <button
        class="select-lang-trigger"
        type="button"
        aria-expanded="false"
        aria-controls="<?= esc_attr($uid) ?>"
        aria-haspopup="true"
        aria-label="<?= esc_attr($label) ?>"
    >
        <span class="select-lang-current"><?= esc_html($current_item["label"]) ?></span>
        <?php component('icon', [
            'name' => 'caret',
            'width' => 11,
            'height' => 7,
            'classes' => 'select-lang-chevron',
        ]); ?>
    </button>

    <ul class="select-lang-list" id="<?= esc_attr($uid) ?>" aria-label="<?= esc_attr($label) ?>" hidden>
        <?php foreach ($items as $item) : ?>
            <li>
                <?php if ($item["disabled"]) : ?>
                    <span
                        class="select-lang-option is-disabled"
                        <?= $item["code"] !== "" ? 'lang="' . esc_attr($item["code"]) . '"' : "" ?>
                        aria-disabled="true"
                    >
                        <?= esc_html($item["label"]) ?>
                    </span>
                <?php else : ?>
                    <a
                        class="select-lang-option<?= $item["current"] ? " is-current" : "" ?>"
                        href="<?= esc_url($item["url"]) ?>"
                        <?= $item["code"] !== "" ? 'lang="' . esc_attr($item["code"]) . '"' : "" ?>
                        <?= $item["current"] ? 'aria-current="page"' : "" ?>
                    >
                        <?= esc_html($item["label"]) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
