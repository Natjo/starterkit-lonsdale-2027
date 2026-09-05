<?php
$root_id = wp_unique_id('snap-demo-');
$slides = [
    ['title' => 'Noir', 'heading' => 'Section noire', 'class' => 'bg-color-1'],
    ['title' => 'Bleu', 'heading' => 'Section bleue', 'class' => 'bg-color-2'],
    ['title' => 'Gris', 'heading' => 'Section grise', 'class' => 'bg-color-3'],
    ['title' => 'Blanc', 'heading' => 'Section blanche', 'class' => ''],
];
?>

<section class="strate strate-snap">
    <?php component('section-snap', [
        'slides' => $slides,
        'root_id' => $root_id,
    ]); ?>

    <?php foreach ($slides as $index => $slide) : ?>
        <section
            id="<?php echo esc_attr($root_id . '-' . ($index + 1)); ?>"
            class="strate strate-snap-panel<?php echo $slide['class'] !== '' ? ' ' . esc_attr($slide['class']) : ''; ?>">
            <div class="snap-panel-content">
                <?php component('title', [
                    'title' => $slide['heading'],
                    'classes' => 'title-1 snap-panel-title',
                ]); ?>
            </div>
        </section>
    <?php endforeach; ?>
</section>
