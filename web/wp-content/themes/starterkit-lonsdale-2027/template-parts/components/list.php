<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'items' => [],
    'card' => 'card-news',
    'type' => 'grid',
    'classes' => '',
    'attributes' => [],
]);

$items = is_int($args['items']) ? [$args['items']] : (is_array($args['items']) ? $args['items'] : []);
if ($items !== [] && !array_is_list($items)) $items = [$items];
$items = array_values(array_filter(
    $items,
    'is_valid_card_data'
));
if ($items === []) return;

$card = resolve_card_name(component_string($args['card']) ?: 'card-news');
if ($card === '') return;

theme_prime_posts($items);
theme_prime_attachments(array_values(array_filter($items, 'is_array')));

$type = strtolower(component_string($args['type']));
$type = in_array($type, ['grid', 'flex'], true) ? $type : 'grid';
$classes = component_classes($type === 'flex' ? 'list-flex' : 'list', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class', 'role']);
?>

<ul class="<?= $classes ?>" role="list"<?= $attributes ?>>
    <?php foreach ($items as $item) : ?>
        <li class="item">
            <?php card($card, $item); ?>
        </li>
    <?php endforeach; ?>
</ul>
