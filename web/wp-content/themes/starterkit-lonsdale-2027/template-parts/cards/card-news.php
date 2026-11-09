<?php
$args = get_template_args();

$link = theme_link($args['link'] ?? []);
$url = $link['url'];
$target = $link['target'];
$tag = $url !== '' ? 'a' : 'div';
?>

<article class="card-news">
    <<?= $tag ?> class="card-content" <?php if ($url !== '') : ?> href="<?= esc_url($url) ?>" <?php if ($target !== '') : ?> target="<?= esc_attr($target) ?>" <?= $target === '_blank' ? ' rel="noopener noreferrer"' : '' ?><?php endif; ?><?php endif; ?>>
        <?php component('picture', [
            'images' => $args['images'] ?? $args['image'] ?? null,
            'size' => $args['image_size'] ?? 'large',
            'fit' => 'cover',
            'classes' => 'card-image',
        ]); ?>

        <div class="card-header">
            <?php component('date', [
                'date' => $args['date'] ?? '',
                'datetime' => $args['datetime'] ?? '',
            ]); ?>
        </div>

        <?php component('title', array_replace($args, [
            'hx' => 3,
            'classes' => 'title-3',
        ])); ?>

        <?php component('text', $args); ?>
    </<?= $tag ?>>
</article>