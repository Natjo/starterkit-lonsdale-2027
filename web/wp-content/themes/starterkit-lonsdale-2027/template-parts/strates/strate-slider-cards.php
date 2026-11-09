<?php
$args = get_template_args();
$rows = is_array($args['items'] ?? null) ? $args['items'] : [];
$items = [];
foreach ($rows as $row) {
    if (!is_array($row) || array_is_list($row)) continue;
    if (is_array($row['card'] ?? null) && !array_is_list($row['card'])) {
        $row = array_replace($row['card'], $row);
    }
    $title = is_scalar($row['title'] ?? null) || ($row['title'] ?? null) instanceof Stringable
        ? trim((string) $row['title'])
        : '';
    $text = is_scalar($row['text'] ?? null) || ($row['text'] ?? null) instanceof Stringable
        ? trim((string) $row['text'])
        : '';
    if ($title === '' && $text === '' && empty($row['image'])) continue;
    $items[] = $row;
}
if ($items === []) return;

$slider_options = is_array($args['slider_options'] ?? null) && !array_is_list($args['slider_options'])
    ? $args['slider_options']
    : [];
$aria_label = is_scalar($args['aria_label'] ?? null) || ($args['aria_label'] ?? null) instanceof Stringable
    ? trim((string) $args['aria_label'])
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Carrousel de cards';
?>

<section <?= options('strate strate-slider-cards', $args) ?>>
    <div class="strate-content">
        <?php component('header', array_replace($args, ['classes' => 'strate-header'])); ?>
        <?php component('slider', array_replace($slider_options, [
            'items' => $items,
            'card' => 'slide',
            'aria_label' => $aria_label,
        ])); ?>
    </div>
</section>
