<?php
$args = get_template_args();
$title = component_string($args['title'] ?? '');
$text = component_string($args['text'] ?? '');
$link = is_array($args['link'] ?? null) && !array_is_list($args['link']) ? $args['link'] : [];
$image = $args['image'] ?? null;
$link_url = component_string($link['url'] ?? '');
$link_title = component_string($link['title'] ?? '');

if ($title === '' && $text === '' && ($link_url === '' || $link_title === '') && empty($image)) return;
?>

<section <?= options('strate strate-push', $args) ?>>
    <?php component('header', [
        'title' => $title,
        'title_classes' => 'title-2 strate-title',
        'text' => $text,
        'text_classes' => 'rte strate-text',
        'link' => $link,
        'classes' => 'strate-body',
    ]); ?>

    <?php component('picture', [
        'image' => $image,
        'size' => '730_auto',
        'classes' => 'strate-media',
    ]); ?>
</section>
