<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'items' => [],
    'aria_label' => 'Navigation des sections',
    'horizontal' => false,
    'classes' => '',
    'attributes' => [],
]);

$horizontal = filter_var(
    $args['horizontal'],
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
) ?? false;

$rows = is_array($args['items']) ? $args['items'] : [];
$items = [];
$seen_targets = [];

foreach ($rows as $row) {
    if (!is_array($row) || array_is_list($row)) continue;

    $label = $row['label'] ?? '';
    $target = $row['target'] ?? '';
    $label = is_scalar($label) || $label instanceof Stringable
        ? trim((string) $label)
        : '';
    $target = is_scalar($target) || $target instanceof Stringable
        ? ltrim(trim((string) $target), '#')
        : '';

    if (
        $label === ''
        || !preg_match('/^[A-Za-z0-9_-]+$/', $target)
        || isset($seen_targets[$target])
    ) {
        continue;
    }

    $seen_targets[$target] = true;
    $items[] = [
        'label' => $label,
        'target' => $target,
    ];
}

if ($items === []) return;

$aria_label = $args['aria_label'];
$aria_label = is_scalar($aria_label) || $aria_label instanceof Stringable
    ? trim((string) $aria_label)
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Navigation des sections';
$classes = component_classes('anchor-nav', $horizontal ? 'horizontal' : '', $args['classes']);
$attributes = component_attributes(
    $args['attributes'],
    ['class', 'aria-label', 'data-module', 'data-context']
);
?>

<nav
    class="<?= $classes ?>"
    aria-label="<?= esc_attr($aria_label) ?>"
    data-module="components/anchor-nav"
    data-context="@visible true"<?= $attributes ?>>
    <ul>
        <?php foreach ($items as $item) : ?>
            <li><a href="#<?= esc_attr($item['target']) ?>"><?= esc_html($item['label']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>
