<?php
$args = get_template_args();
$image_size = 'full';
$marquee_options = is_array($args['marquee_options'] ?? null) ? $args['marquee_options'] : [];
$to_bool = static fn(mixed $value, bool $default = false): bool => filter_var(
    $value,
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
) ?? $default;
$hydrate = $to_bool($marquee_options['hydrate'] ?? true, true);
$media = theme_string($marquee_options['media'] ?? null);
$content_type = array_key_exists('content_type', $args)
    ? theme_string($args['content_type'])
    : 'images';
$content_type = in_array($content_type, ['html', 'cards', 'images'], true) ? $content_type : 'images';
$items = [];
$card = null;
$item_component = null;

if ($content_type === 'images') {
    $rows = is_array($args['images'] ?? null) ? $args['images'] : [];

    foreach ($rows as $row) {
        if (!is_array($row) || empty($row['image'])) continue;

        $items[] = [
            'image' => $row['image'],
            'size' => $image_size,
            'fit' => 'cover',
            'classes' => 'marquee-image',
        ];
    }

    $item_component = 'picture';
} elseif ($content_type === 'cards') {
    $post_type = sanitize_key(theme_string($args['post_type'] ?? ''));
    $card = sanitize_key(theme_string($args['card'] ?? $post_type));
    $card_exists = resolve_card_name($card) !== '';
    $post_type_exists = $post_type !== '' && post_type_exists($post_type);
    $selected = is_array($args['items'] ?? null) ? $args['items'] : [];
    $seen_ids = [];

    if ($card_exists && ($post_type_exists || array_key_exists('card', $args))) {
        theme_prime_posts($selected);

        foreach ($selected as $item) {
            $id = filter_var($item, FILTER_VALIDATE_INT);
            if (
                $id !== false
                && $id > 0
                && $post_type_exists
                && get_post_type($id) === $post_type
                && !isset($seen_ids[$id])
            ) {
                $items[] = $id;
                $seen_ids[$id] = true;
                continue;
            }

            if (!is_array($item) || !is_valid_card_data($item)) continue;

            $item_post_id = (int) ($item['post_id'] ?? 0);
            if ($item_post_id > 0 && (!$post_type_exists || get_post_type($item_post_id) !== $post_type)) {
                continue;
            }

            $items[] = $item;
        }
    }

} else {
    $items = is_array($args['items'] ?? null) ? $args['items'] : [];
}

if ($items === []) return;

$component_options = array_replace($marquee_options, [
    'items' => $items,
    'card' => $card,
    'item_component' => $item_component,
    'hydrate' => false,
]);
$module_attributes = html_attributes([
    'data-module' => $hydrate ? 'strates/strate-marquee' : null,
    'data-context' => $hydrate ? '@visible true' : null,
    'data-module-media' => $hydrate && $media !== '' ? $media : null,
]);
?>

<section <?= options('strate strate-marquee', $args) ?><?= $module_attributes !== '' ? ' ' . $module_attributes : '' ?>>
    <div class="strate-content">
        <?php component('header', array_replace($args, ['classes' => 'strate-header'])); ?>
        <?php component('marquee', $component_options); ?>
    </div>
</section>
