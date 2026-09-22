<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'slides' => [],
    'root_id' => '',
    'aria_label' => 'Navigation des sections',
    'classes' => '',
    'attributes' => [],
]);

$root_id = $args['root_id'];
$root_id = is_scalar($root_id) || $root_id instanceof Stringable
    ? trim((string) $root_id)
    : '';

if ($root_id === '' || !preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $root_id)) return;

$slides = is_array($args['slides']) ? array_values($args['slides']) : [];
$items = [];

foreach ($slides as $index => $slide) {
    if (!is_array($slide) || array_is_list($slide)) continue;

    $title = $slide['title'] ?? '';
    $title = is_scalar($title) || $title instanceof Stringable
        ? trim((string) $title)
        : '';

    if ($title === '') continue;

    $items[] = [
        'title' => $title,
        'target' => $root_id . '-' . ($index + 1),
    ];
}

if ($items === []) return;

$aria_label = $args['aria_label'];
$aria_label = is_scalar($aria_label) || $aria_label instanceof Stringable
    ? trim((string) $aria_label)
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Navigation des sections';
$classes = component_classes('snap-nav', $args['classes']);
$attributes = component_attributes(
    $args['attributes'],
    ['class', 'aria-label', 'data-module', 'data-context']
);
?>

<nav
    class="<?= $classes ?>"
    aria-label="<?= esc_attr($aria_label) ?>"
    data-module="components/section-snap"
    data-context="@visible true"<?= $attributes ?>>
    <ul>
        <?php foreach ($items as $item) : ?>
            <li><a href="#<?= esc_attr($item['target']) ?>"><?= esc_html($item['title']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>
