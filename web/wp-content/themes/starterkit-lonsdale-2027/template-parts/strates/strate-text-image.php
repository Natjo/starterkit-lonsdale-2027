<?php
$args = get_template_args(['title' => '', 'text' => '', 'items' => []]);
$args['title'] = component_string($args['title']);
$args['text'] = component_string($args['text']);

$classes = [
    'strate',
    'strate-text-image',
    !empty($args['reverse']) ? 'reverse' : '',
    !empty($args['center']) ? 'center' : '',
];
if ($args['title'] === '' && $args['text'] === '' && empty($args['image'])) return;
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
