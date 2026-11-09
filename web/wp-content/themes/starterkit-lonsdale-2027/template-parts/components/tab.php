<?php
/** @var array<int|string, mixed>|null $params */

$params = component_args($params ?? null);
if ($params === null) return;

$args = normalize_args($params, [
    'items' => [],
    'id' => '',
    'title' => '',
    'aria_label' => 'Onglets',
    'orientation' => 'horizontal',
    'classes' => '',
    'attributes' => [],
]);

$raw_items = component_list($args['items']);
$items = [];
foreach ($raw_items as $item) {
    if (!is_array($item)) continue;

    $item_title = theme_string($item['title'] ?? '');
    if ($item_title === '') continue;

    $content = $item['content'] ?? '';
    $items[] = [
        'title' => $item_title,
        'content' => is_scalar($content) || $content instanceof Stringable ? (string) $content : '',
        'selected' => component_bool($item['selected'] ?? false),
        'disabled' => component_bool($item['disabled'] ?? false),
        'tab_attributes' => is_array($item['tab_attributes'] ?? null) ? $item['tab_attributes'] : [],
        'panel_attributes' => is_array($item['panel_attributes'] ?? null) ? $item['panel_attributes'] : [],
    ];
}
if ($items === []) return;

$active_index = null;
foreach ($items as $index => $item) {
    if (!$item['disabled'] && $item['selected']) {
        $active_index = $index;
        break;
    }
}
if ($active_index === null) {
    foreach ($items as $index => $item) {
        if (!$item['disabled']) {
            $active_index = $index;
            break;
        }
    }
}
if ($active_index === null) return;

$base_id = component_id($args['id'], 'tabs-');
$title_id = $base_id . '-title';
$title = theme_string($args['title']);
$aria_label = theme_string($args['aria_label']) ?: 'Onglets';
$orientation = strtolower(theme_string($args['orientation']));
$orientation = in_array($orientation, ['horizontal', 'vertical'], true) ? $orientation : 'horizontal';
$classes = component_classes('tabs', $args['classes']);
$attributes = component_attributes($args['attributes'], ['id', 'class', 'data-module', 'data-context']);
?>

<div id="<?= esc_attr($base_id) ?>" class="<?= $classes ?>" data-module="components/tab" data-context="@visible true"<?= $attributes ?>>
    <?php if ($title !== '') : ?><h2 id="<?= esc_attr($title_id) ?>" class="tabs-title"><?= esc_html($title) ?></h2><?php endif; ?>
    <div class="tabs-list" role="tablist" aria-orientation="<?= esc_attr($orientation) ?>"<?= $title !== '' ? ' aria-labelledby="' . esc_attr($title_id) . '"' : ' aria-label="' . esc_attr($aria_label) . '"' ?>>
        <?php foreach ($items as $index => $item) :
            $tab_id = $base_id . '-tab-' . $index;
            $panel_id = $base_id . '-panel-' . $index;
            $active = $index === $active_index;
            $tab_attributes = component_attributes($item['tab_attributes'], [
                'id', 'type', 'role', 'aria-controls', 'aria-selected', 'tabindex', 'disabled',
            ]);
        ?>
            <button id="<?= esc_attr($tab_id) ?>" type="button" role="tab" aria-controls="<?= esc_attr($panel_id) ?>" aria-selected="<?= $active ? 'true' : 'false' ?>" tabindex="<?= $active ? '0' : '-1' ?>"<?= $item['disabled'] ? ' disabled' : '' ?><?= $tab_attributes ?>><?= esc_html($item['title']) ?></button>
        <?php endforeach; ?>
    </div>
    <?php foreach ($items as $index => $item) :
        $tab_id = $base_id . '-tab-' . $index;
        $panel_id = $base_id . '-panel-' . $index;
        $active = $index === $active_index;
        $panel_attributes = component_attributes($item['panel_attributes'], [
            'id', 'class', 'role', 'aria-labelledby', 'tabindex', 'hidden',
        ]);
    ?>
        <div id="<?= esc_attr($panel_id) ?>" class="tabs-panel" role="tabpanel" aria-labelledby="<?= esc_attr($tab_id) ?>" tabindex="0"<?= $active ? '' : ' hidden' ?><?= $panel_attributes ?>><?= wp_kses_post($item['content']) ?></div>
    <?php endforeach; ?>
</div>
