<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'items' => [],
    'card' => 'card-news',
    'type' => 'grid',
    'classes' => '',
    'attributes' => [],
]);
$to_string = static fn(mixed $value): string => is_scalar($value) || $value instanceof Stringable
    ? trim((string) $value)
    : '';

$items = is_int($args['items']) ? [$args['items']] : (is_array($args['items']) ? $args['items'] : []);
if ($items !== [] && !array_is_list($items)) $items = [$items];
$items = array_values(array_filter(
    $items,
    'is_valid_card_data'
));
if ($items === []) return;

$card = resolve_card_name($to_string($args['card']) ?: 'card-news');
if ($card === '') return;

theme_prime_posts($items);
theme_prime_attachments(array_values(array_filter($items, 'is_array')));

$type = strtolower($to_string($args['type']));
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
