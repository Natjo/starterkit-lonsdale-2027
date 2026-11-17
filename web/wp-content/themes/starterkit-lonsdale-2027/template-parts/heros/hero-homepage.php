<?php
$args = get_template_args();
?>

<header class="hero hero-homepage">
    <div class="hero-content">
        <?php component('eyebrow', [
            'eyebrow' => $args['suptitle'] ?? '',
        ]); ?>

        <?php component('title', array_replace($args, [
            'hx' => 1,
            'classes' => 'title-1 hero-title',
        ])); ?>

        <?php component('text', [
            'text' => $args['text'] ?? '',
            'classes' => 'hero-text',
        ]); ?>
        
        <?php component('btn', [
            'link' => $args['link'] ?? null,
            'classes' => 'btn-solid hero-link',
        ]); ?>
    </div>

    <?php component('picture', [
        'images' => $args['images'] ?? null,
        'classes' => 'hero-bg',
        'cover' => true,
        'preload' => true,
    ]); ?>
</header>