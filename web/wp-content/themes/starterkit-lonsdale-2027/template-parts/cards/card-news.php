<?php
$args = get_template_args();

$link = theme_link($args['link'] ?? []);
$url = $link['url'];
$target = $link['target'];
$tag = $url !== '' ? 'a' : 'div';
$images = $args['images'] ?? $args['image'] ?? null;
$has_source = static function (mixed $source): bool {
    if (is_int($source)) return $source > 0;
    return (is_scalar($source) || $source instanceof Stringable)
        && trim((string) $source) !== '' && trim((string) $source) !== '0';
};
$has_image = is_array($images)
    ? $has_source($images['desktop'] ?? null) || $has_source($images['mobile'] ?? null)
    : $has_source($images);
$picture = [
    'images' => $has_image ? $images : rtrim(THEME_ASSETS, '/') . '/img/placeholder.svg',
    'size' => $args['image_size'] ?? 'large',
    'cover' => true,
    'classes' => 'card-image',
];
if (!$has_image) $picture['alt'] = '';
?>

<article class="card-news">
    <<?= $tag ?> class="card-content" <?php if ($url !== '') : ?> href="<?= esc_url($url) ?>" <?php if ($target !== '') : ?> target="<?= esc_attr($target) ?>" <?= $target === '_blank' ? ' rel="noopener noreferrer"' : '' ?><?php endif; ?><?php endif; ?>>
        <?php component('picture', $picture); ?>

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
