<?php
$args = get_template_args();
$link = is_array($args['link'] ?? null) && !array_is_list($args['link']) ? $args['link'] : [];
$url = is_scalar($link['url'] ?? null) || ($link['url'] ?? null) instanceof Stringable
    ? trim((string) $link['url'])
    : '';
$tag = $url !== '' ? 'a' : 'div';
?>

<div class="card-team">
    <<?= $tag ?><?php if ($url !== '') : ?> href="<?= esc_url($url) ?>" <?php endif; ?>>
        <?php component('picture', [
            'image' => $args['image'] ?? null,
            'size' => $args['image_size'] ?? 'large',
            'fit' => 'cover',
            'classes' => 'card-image',
        ]); ?>
        <div class="card-content">
            <?php component('title', array_replace($args, ['hx' => 3, 'classes' => 'title-3'])); ?>
            <?php component('text', $args); ?>
        </div>
    </<?= $tag ?>>
</div>