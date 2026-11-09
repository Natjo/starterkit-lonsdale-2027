<?php
$args = get_template_args();

$media_type = is_string($args['media_type'] ?? null) ? strtolower(trim($args['media_type'])) : '';

if (!in_array($media_type, ['image', 'video'], true)) {
    $media_type = array_key_exists('is_image', $args) && empty($args['is_image'])
        ? 'video'
        : 'image';
}

$source = $media_type === 'image' ? ($args['image'] ?? null) : ($args['video'] ?? null);
$has_source = is_scalar($source) || $source instanceof Stringable ? trim((string) $source) !== '' : false;

if (!$has_source) {
    return;
}
?>

<section <?= options('strate strate-media', $args) ?>>
    <?php if ($media_type === 'image') : ?>
        <?php component('picture', [
            'image' => $source,
            'classes' => 'image'
        ]); ?>
    <?php else : ?>
        <?php component('video', [
            'url' => $source,
            'title' => $args['video_title'] ?? $args['video-title'] ?? '',
        ]); ?>
    <?php endif; ?>
</section>