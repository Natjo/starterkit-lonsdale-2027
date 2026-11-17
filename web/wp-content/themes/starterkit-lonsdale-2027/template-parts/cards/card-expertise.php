<?php
$args = get_template_args();

$title = theme_string($args['title'] ?? '');
if ($title === '') return;
?>

<div class="card-expertise">
    <?php component('title', [
        'title' => $title,
        'hx' => 3,
        'classes' => 'title-3',
    ]); ?>

    <?php component('text', [
        'text' => $args['text'] ?? '',
        'classes' => 'card-text',
    ]); ?>

    <?php component('btn', [
        'link' => [
            'url' => $args['link']['url'],
            'title' => 'See more',
        ],
        'classes' => 'btn-solid',
    ]); ?>
</div>