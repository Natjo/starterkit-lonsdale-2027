<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'items' => [],
    'card' => 'card-news',
    'item_component' => null,
    'classes' => '',
    'navigation' => true,
    'pagination' => true,
    'timeline' => false,
    'hydrate' => true,
    'media' => null,
    'attributes' => [],
    'aria_label' => 'Carrousel',
    'prev_label' => 'Diapositive précédente',
    'next_label' => 'Diapositive suivante',
    'pagination_label' => 'Navigation du carrousel',
    'slide_label' => 'Diapositive',
    'slide_position_format' => '{label} {current} sur {total}',
    'page_label' => 'Page',
    'go_to_label' => 'Aller à la page',
]);

$with_fallback = static function (mixed $value, string $fallback): string {
    $value = component_string($value);

    return $value !== '' ? $value : $fallback;
};

$items = component_list($args['items']);

$item_component = component_string($args['item_component']);
$item_component = $item_component === 'picture' ? 'picture' : null;
$card_value = $args['card'];
$card = $card_value === null ? null : component_string($card_value);
$card = $card === '' && $card_value !== null ? 'card-news' : $card;

if ($item_component !== null) {
    $items = array_values(array_filter(
        $items,
        static fn(mixed $item): bool => is_array($item) && $item !== [] && !array_is_list($item)
    ));
} elseif ($card === null) {
    $items = array_values(array_filter(array_map(
        static fn(mixed $item): ?string => is_scalar($item) || $item instanceof Stringable
            ? (trim((string) $item) !== '' ? (string) $item : null)
            : null,
        $items
    )));
} else {
    $items = array_values(array_filter(
        $items,
        'is_valid_card_data'
    ));
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

$hydrate = component_bool($args['hydrate'], true);
$has_multiple_items = count($items) > 1;
$navigation = $has_multiple_items && component_bool($args['navigation'], true);
$pagination = $has_multiple_items && component_bool($args['pagination'], true);
$timeline = $has_multiple_items && component_bool($args['timeline']);
$media = component_string($args['media']);
$classes = component_classes('slider', $args['classes']);

$aria_label = $with_fallback($args['aria_label'], 'Carrousel');
$prev_label = $with_fallback($args['prev_label'], 'Diapositive précédente');
$next_label = $with_fallback($args['next_label'], 'Diapositive suivante');
$pagination_label = $with_fallback($args['pagination_label'], 'Navigation du carrousel');
$slide_label = $with_fallback($args['slide_label'], 'Diapositive');
$slide_position_format = $with_fallback($args['slide_position_format'], '{label} {current} sur {total}');
$page_label = $with_fallback($args['page_label'], 'Page');
$go_to_label = $with_fallback($args['go_to_label'], 'Aller à la page');

$region_attributes = html_attributes([
    'class' => $classes,
    'role' => 'region',
    'aria-roledescription' => 'carousel',
    'aria-label' => $aria_label,
    'data-module' => $hydrate ? 'components/slider' : null,
    'data-context' => $hydrate ? '@visible true' : null,
    'data-module-media' => $hydrate && $media !== '' ? $media : null,
    'data-slider-page-label' => $has_multiple_items ? $page_label : null,
    'data-slider-go-to-label' => $has_multiple_items ? $go_to_label : null,
]);
$attributes = component_attributes($args['attributes'], [
    'class',
    'role',
    'aria-label',
    'aria-roledescription',
    'data-module',
    'data-context',
    'data-module-media',
    'data-slider-page-label',
    'data-slider-go-to-label',
]);
$item_count = count($items);
?>

<div <?= $region_attributes ?><?= $attributes ?>>
    <?php if ($navigation) : ?>
        <div class="slider-navigation">
            <button class="slider-btn prev" type="button" aria-label="<?= esc_attr($prev_label) ?>">
                <span aria-hidden="true">&lt;</span>
            </button>
            <button class="slider-btn next" type="button" aria-label="<?= esc_attr($next_label) ?>">
                <span aria-hidden="true">&gt;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="slider-wrapper">
        <ul
            class="slider-content"
            role="list"
            aria-label="<?= esc_attr($aria_label) ?>"
            data-lenis-prevent-horizontal<?= $has_multiple_items ? ' tabindex="0"' : '' ?>>
            <?php foreach ($items as $index => $item) :
                $slide_position = strtr($slide_position_format, [
                    '{label}' => $slide_label,
                    '{current}' => (string) ($index + 1),
                    '{total}' => (string) $item_count,
                ]);
            ?>
                <li
                    class="item"
                    role="listitem"
                    aria-roledescription="slide"
                    aria-label="<?= esc_attr($slide_position) ?>">
                    <?php if ($item_component !== null) : ?>
                        <?php component($item_component, $item); ?>
                    <?php elseif ($card === null) : ?>
                        <?= wp_kses_post($item) ?>
                    <?php else : ?>
                        <?php card($card, $item); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($pagination) : ?>
        <div class="slider-pagination" role="group" aria-label="<?= esc_attr($pagination_label) ?>" data-slider-pagination></div>
    <?php endif; ?>

    <?php if ($timeline) : ?>
        <div class="slider-timeline" aria-hidden="true" data-slider-timeline>
            <span class="slider-timeline-progress"></span>
        </div>
    <?php endif; ?>

    <?php if ($has_multiple_items && ($hydrate || $navigation || $pagination || $timeline)) : ?>
        <div class="sr-only" aria-live="polite" aria-atomic="true" data-slider-status></div>
    <?php endif; ?>
</div>
