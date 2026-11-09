<?php
$args = get_template_args(['title' => '', 'text' => '', 'items' => []]);
$args['title'] = theme_string($args['title']);
$args['text'] = theme_string($args['text']);

if ($args['text'] === '') return;
?>

<section <?= options("strate strate-intro", $args) ?>>
    <div class="strate-content">
        <?php component('text', [
            'text' => $args['text'],
            'classes' => 'rte strate-text',
        ]) ?>
    </div>
</section>
