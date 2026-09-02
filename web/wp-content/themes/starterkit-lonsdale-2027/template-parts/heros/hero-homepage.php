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
            'classes' => 'title-1',
        ])); ?>
        <?php component('text', [
            'text' => $args['text'] ?? '',
            'classes' => 'intro',
        ]); ?>
    </div>

    <?php component('picture', [
        'images' => $args['images'] ?? null,
        'classes' => 'bg',
        'preload' => true,
    ]); ?>
</header>
