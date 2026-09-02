<?php
$args = get_template_args();
?>

<header class="hero hero-news">
    <div class="hero-content">
        <?php component('date', [
            'date' => $args['date'] ?? '',
            'classes' => 'news-date',
        ]); ?>

        <?php component('title', [
            'title' => $args['title'] ?? '',
            'hx' => 1,
            'classes' => 'title-1 news-title',
        ]); ?>
        <?php component('picture', [
            'images' => $args['images'] ?? [],
            'classes' => 'news-image',
        ]); ?>
    </div>
</header>
