<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'total' => 0,
    'current' => 1,
    'prev_text' => '‹',
    'next_text' => '›',
    'aria_label' => 'Pagination',
    'base' => '',
    'classes' => '',
    'attributes' => [],
]);


$total = is_numeric($args['total']) ? max(0, (int) $args['total']) : 0;
if ($total <= 1) return;

$current = is_numeric($args['current']) ? (int) $args['current'] : 1;
$current = max(1, min($total, $current));

$pagination_args = [
    'total' => $total,
    'current' => $current,
    'type' => 'array',
    'prev_text' => esc_html(component_string($args['prev_text']) ?: '‹'),
    'next_text' => esc_html(component_string($args['next_text']) ?: '›'),
];
$base = component_string($args['base']);
if ($base !== '' && str_contains($base, '%#%')) {
    $pagination_args['base'] = $base;
    $pagination_args['format'] = '';
}
$links = paginate_links($pagination_args);
$links = is_array($links) ? array_values(array_filter($links, 'is_string')) : [];
if ($links === []) return;

$aria_label = component_string($args['aria_label']) ?: 'Pagination';
$classes = component_classes('pagination', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'aria-label']);
$allowed_html = [
    'a' => [
        'class' => true,
        'href' => true,
    ],
    'span' => [
        'aria-current' => true,
        'class' => true,
    ],
];
?>

<nav class="<?= $classes ?>" aria-label="<?= esc_attr($aria_label) ?>"<?= $attributes ?>>
    <ul class="pagination-list" role="list">
        <?php foreach ($links as $link) : ?>
            <li class="pagination-item"><?= wp_kses($link, $allowed_html) ?></li>
        <?php endforeach; ?>
    </ul>
</nav>
