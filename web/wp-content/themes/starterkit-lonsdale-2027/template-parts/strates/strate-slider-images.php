<?php
$args = get_template_args();
$rows = is_array($args['images'] ?? null) ? $args['images'] : [];
$items = [];
foreach ($rows as $row) {
    if (!is_array($row) || empty($row['image'])) continue;
    $items[] = [
        'image' => $row['image'],
        'size' => 'full',
        'cover' => true,
        'classes' => 'slider-image',
    ];
}
if ($items === []) return;

$slider_options = is_array($args['slider_options'] ?? null) && !array_is_list($args['slider_options'])
    ? $args['slider_options']
    : [];
$aria_label = is_scalar($args['aria_label'] ?? null) || ($args['aria_label'] ?? null) instanceof Stringable
    ? trim((string) $args['aria_label'])
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Galerie d’images';
?>

<section <?= options('strate strate-slider-images', $args) ?>>
    <div class="strate-content">
        <?php component('header', array_replace($args, ['classes' => 'strate-header'])); ?>
        <?php component('slider', array_replace($slider_options, [
            'items' => $items,
            'item_component' => 'picture',
            'card' => null,
            'aria_label' => $aria_label,
        ])); ?>
    </div>
</section>
