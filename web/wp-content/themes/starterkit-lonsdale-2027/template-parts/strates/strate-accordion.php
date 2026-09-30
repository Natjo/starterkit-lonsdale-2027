<?php
$args = get_template_args();
?>

<section <?= options("strate strate-accordion", $args) ?>>
    <div class="strate-inner">
        <div class="strate-header">
            <?php
            component('title', [
                'title' => $args['title'] ?? '',
                'classes' => 'title-2 strate-title',
            ])
            ?>

            <?php component('text', [
                'text' => $args['text'],
                'classes' => 'rte strate-text',
            ]) ?>
        </div>

        <?php component('accordion', [
            'items' => $args['items'],
            'multiple' => false
        ]); ?>
    </div>
</section>