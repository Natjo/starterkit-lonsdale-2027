<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'items' => [],
    'aria_label' => 'Fil d’Ariane',
    'classes' => '',
    'attributes' => [],
]);

$items = is_array($args['items']) ? $args['items'] : [];
$normalized_items = [];

foreach ($items as $item) {
    if (!is_array($item) || array_is_list($item)) continue;

    $name = $item['name'] ?? '';
    $name = is_scalar($name) || $name instanceof Stringable
        ? trim((string) $name)
        : '';

    if ($name === '') continue;

    $url = $item['url'] ?? '';
    $url = is_scalar($url) || $url instanceof Stringable
        ? esc_url(trim((string) $url))
        : '';

    $normalized_items[] = [
        'name' => $name,
        'url' => $url,
    ];
}

if (count($normalized_items) < 2) return;

$aria_label = $args['aria_label'];
$aria_label = is_scalar($aria_label) || $aria_label instanceof Stringable
    ? trim((string) $aria_label)
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Fil d’Ariane';

$classes = component_classes('breadcrumb', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'aria-label']);
$last_index = array_key_last($normalized_items);
?>

<nav class="<?= $classes ?>" aria-label="<?= esc_attr($aria_label) ?>"<?= $attributes ?>>
    <ol class="breadcrumb-list">
        <?php foreach ($normalized_items as $index => $item) : ?>
            <li class="breadcrumb-item">
                <?php if ($index === $last_index) : ?>
                    <span aria-current="page"><?= esc_html($item['name']) ?></span>
                <?php elseif ($item['url'] === '') : ?>
                    <span><?= esc_html($item['name']) ?></span>
                <?php else : ?>
                    <a href="<?= $item['url'] ?>"><?= esc_html($item['name']) ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
