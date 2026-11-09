<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$items_input = component_list($params['items'] ?? null);

$item_component = theme_string($params['item_component'] ?? null);
$item_component = $item_component === 'picture' ? 'picture' : null;
$card_value = $params['card'] ?? null;
$card = $card_value === null ? null : resolve_card_name(theme_string($card_value));

if ($item_component !== null) {
    $items = array_values(array_filter(
        $items_input,
        static fn(mixed $item): bool => is_array($item) && $item !== [] && !array_is_list($item)
    ));
} elseif ($card_value !== null) {
    if ($card === '') return;

    $items = array_values(array_filter($items_input, 'is_valid_card_data'));
} else {
    $items = array_values(array_filter(array_map(
        static function (mixed $item): ?string {
            if (!is_scalar($item) && !$item instanceof Stringable) {
                return null;
            }

            $item = trim((string) $item);

            return $item !== '' ? $item : null;
        },
        $items_input
    )));
}

if ($items === []) {
    return;
}

if ($item_component !== null) {
    theme_prime_attachments($items);
} elseif ($card !== null) {
    theme_prime_posts($items);
    theme_prime_attachments(array_values(array_filter($items, 'is_array')));
}


$speed = is_numeric($params['speed'] ?? null) ? (float) $params['speed'] : 60.0;
$speed = max(10.0, min(400.0, $speed));
$direction = ($params['direction'] ?? '') === 'right' ? 'right' : 'left';
$hydrate = component_bool($params['hydrate'] ?? true, true);
$aria_label = $params['aria_label'] ?? 'Contenu défilant';
$aria_label = is_scalar($aria_label) || $aria_label instanceof Stringable
    ? trim((string) $aria_label)
    : '';
$aria_label = $aria_label !== '' ? $aria_label : 'Contenu défilant';
$classes = component_classes('marquee', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'role',
    'aria-label',
    'data-module',
    'data-context',
    'data-marquee-speed',
    'data-marquee-direction',
    'data-marquee-scroll-acceleration',
    'data-marquee-follow-scroll-direction',
    'data-marquee-pause-hover',
    'data-marquee-pause-focus',
]);
$module_attributes = html_attributes([
    'data-module' => $hydrate ? 'components/marquee' : null,
    'data-context' => $hydrate ? '@visible true' : null,
]);
?>

<div
    class="<?= $classes ?>"
    role="region"
    aria-label="<?= esc_attr($aria_label) ?>"
    data-marquee-speed="<?= esc_attr((string) $speed) ?>"
    data-marquee-direction="<?= esc_attr($direction) ?>"
    data-marquee-scroll-acceleration="<?= component_bool($params['scroll_acceleration'] ?? true, true) ? 'true' : 'false' ?>"
    data-marquee-follow-scroll-direction="<?= component_bool($params['follow_scroll_direction'] ?? true, true) ? 'true' : 'false' ?>"
    data-marquee-pause-hover="<?= component_bool($params['pause_on_hover'] ?? true, true) ? 'true' : 'false' ?>"
    data-marquee-pause-focus="<?= component_bool($params['pause_on_focus'] ?? true, true) ? 'true' : 'false' ?>"<?= $module_attributes !== '' ? ' ' . $module_attributes : '' ?><?= $attributes ?>>
    <div class="marquee-track" data-marquee-track>
        <div class="marquee-group" data-marquee-group>
            <?php foreach ($items as $item) : ?>
                <div class="marquee-item">
                    <?php if ($item_component !== null) : ?>
                        <?php component($item_component, $item); ?>
                    <?php elseif ($card !== null) : ?>
                        <?php card($card, $item); ?>
                    <?php else : ?>
                        <?= wp_kses_post($item) ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
