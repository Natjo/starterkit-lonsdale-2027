<?php
$args = get_template_args();
?>

<section <?= options("strate strate-text", $args) ?>>
    <div class="strate-inner">
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
</section>