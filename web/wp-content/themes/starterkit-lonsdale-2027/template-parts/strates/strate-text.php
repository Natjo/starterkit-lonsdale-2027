<?php
$args = get_template_args();
?>

<section <?= options("strate strate-text", $args) ?>>
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
</section>
