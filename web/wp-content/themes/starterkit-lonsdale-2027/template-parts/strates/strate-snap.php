<?php
$args = get_template_args();
$rows = is_array($args['slides'] ?? null) ? $args['slides'] : [];
$slides = [];

foreach ($rows as $row) {
    if (!is_array($row) || array_is_list($row)) continue;

    $title = theme_string($row['title'] ?? '');
    if ($title === '') continue;

    $background = theme_string($row['background_color'] ?? '');
    $slides[] = [
        'title' => $title,
        'text' => $row['text'] ?? '',
        'image' => $row['image'] ?? null,
        'link' => $row['link'] ?? null,
        'background' => in_array($background, ['color-1', 'color-2', 'color-3'], true) ? $background : '',
    ];
}

if ($slides === []) return;

$root_id = wp_unique_id('snap-');
?>

<section <?= options('strate strate-snap', $args) ?>>
    <?php component('section-snap', [
        'slides' => $slides,
        'root_id' => $root_id,
    ]); ?>

    <?php foreach ($slides as $index => $slide) : ?>
        <section
            id="<?= esc_attr($root_id . '-' . ($index + 1)) ?>"
            class="strate strate-snap-panel<?= $slide['background'] !== '' ? ' bg-' . esc_attr($slide['background']) : '' ?>">
            <div class="snap-panel-content">
                <div class="snap-panel-body">
                    <?php component('title', [
                        'title' => $slide['title'],
                        'classes' => 'title-1 snap-panel-title',
                    ]); ?>
                    <?php component('text', [
                        'text' => $slide['text'],
                        'classes' => 'rte snap-panel-text',
                    ]); ?>
                    <?php if (is_array($slide['link']) && !array_is_list($slide['link'])) : ?>
                        <?php component('btn', ['link' => $slide['link'], 'classes' => 'snap-panel-link']); ?>
                    <?php endif; ?>
                </div>
                <?php component('picture', [
                    'image' => $slide['image'],
                    'size' => 'large',
                    'classes' => 'snap-panel-image',
                ]); ?>
            </div>
        </section>
    <?php endforeach; ?>
</section>
