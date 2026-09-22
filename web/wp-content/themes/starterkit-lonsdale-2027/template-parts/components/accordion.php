<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$accordion_list = component_list($params['items'] ?? null);
if ($accordion_list === []) {
    return;
}

$items = array_values(array_filter(array_map(static function (mixed $item): ?array {
    if (!is_array($item)) return null;

    $title = $item['title'] ?? '';
    $text = $item['text'] ?? '';
    if ((!is_scalar($title) && !$title instanceof Stringable)
        || (!is_scalar($text) && !$text instanceof Stringable)) {
        return null;
    }

    $title = trim((string) $title);
    $text = trim((string) $text);
    return $title !== '' && $text !== '' ? ['title' => $title, 'text' => $text] : null;
}, $accordion_list)));

if ($items === []) {
    return;
}

$multiple = filter_var($params['multiple'] ?? false, FILTER_VALIDATE_BOOL);
$classes = component_classes('accordion', $params['classes'] ?? '');
$attributes = component_attributes($params['attributes'] ?? [], [
    'class',
    'data-module',
    'data-context',
    'data-multiple',
]);
$uid = component_id('', 'accordion-');
?>
<div class="<?= $classes ?>"<?= $attributes ?> data-module="components/accordion" data-context="@visible true" data-multiple="<?= $multiple ? 'true' : 'false' ?>">
    <?php foreach ($items as $index => $item) :
        $button_id = $uid . '-summary-' . $index;
        $panel_id = $uid . '-panel-' . $index;
        $is_open = $multiple || $index === 0;
    ?>
        <div class="details">
            <h3>
                <button id="<?= esc_attr($button_id) ?>" class="summary" type="button" aria-expanded="<?= $is_open ? 'true' : 'false' ?>" aria-controls="<?= esc_attr($panel_id) ?>">
                    <span><?= esc_html($item['title']) ?></span>
                    <span class="accordion-caret" aria-hidden="true"></span>
                </button>
            </h3>

            <div id="<?= esc_attr($panel_id) ?>" class="details-content" role="region" aria-labelledby="<?= esc_attr($button_id) ?>" aria-hidden="<?= $is_open ? 'false' : 'true' ?>">
                <div class="details-content-inner">
                    <div class="text rte">
                        <?= wp_kses_post($item['text']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>
