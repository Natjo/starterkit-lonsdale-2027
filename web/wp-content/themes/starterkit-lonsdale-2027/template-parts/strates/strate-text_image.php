<?php
$args = get_template_args();

$classes = [
    'strate',
    'strate-text_image',
    !empty($args['reverse']) ? 'reverse' : '',
    !empty($args['center']) ? 'center' : '',
];
?>

<section <?= options($classes, $args) ?>>
    <div class="strate-inner">
        <div class="strate-content">
            <?php component('title', [
                'title' => $args['title'],
                'classes' => 'title-2 strate-title',
            ]); ?>

            <?php component('text', [
                'text' => $args['text'],
                'classes' => 'rte strate-text',
            ]) ?>
        </div>

        <?php component('picture', [
            'image' => $args['image'] ?? null,
            'size' => '730_auto',
            'classes' => 'strate-media',
        ]) ?>
    </div>
</section>