<?php
$args = get_template_args();
$items = is_array($args['items'] ?? null) ? $args['items'] : [];
$items = array_map(static function ($item) {
    if (!is_array($item) || !is_array($item['card'] ?? null) || array_is_list($item['card'])) return $item;
    return array_replace($item['card'], $item);
}, $items);
$items = array_values(array_filter(
    $items,
    static fn(mixed $item): bool => is_array($item)
        && !array_is_list($item)
        && (
            (is_scalar($item['value'] ?? null) || ($item['value'] ?? null) instanceof Stringable)
            && trim((string) $item['value']) !== ''
        )
));

if ($items === []) return;
?>

<section <?= options('strate strate-keynumbers', $args) ?>>
    <div class="strate-content">
        <?php component('header', array_replace($args, ['classes' => 'strate-header'])); ?>
        <?php component('list', [
            'items' => $items,
            'card' => 'keynumber',
            'classes' => 'keynumbers-list',
        ]); ?>
    </div>
</section>
