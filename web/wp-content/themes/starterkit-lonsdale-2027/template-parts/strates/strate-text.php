<?php
$args = get_template_args(['title' => '', 'text' => '', 'items' => []]);
$args['title'] = theme_string($args['title']);
$args['text'] = theme_string($args['text']);
if ($args['title'] === '' && $args['text'] === '') return;
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