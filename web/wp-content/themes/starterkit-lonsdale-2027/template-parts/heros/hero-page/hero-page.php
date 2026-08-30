<?php
$args = get_template_args();
?>

<header class="hero hero-page">
    <div class="hero-content">
        <?php component('title', array_replace($args, [
            'hx' => 1,
            'classes' => 'title-1',
        ])); ?>
        <?php component('text', [
            'text' => $args['text'] ?? '',
            'classes' => 'intro',
        ]); ?>
    </div>
</header>
