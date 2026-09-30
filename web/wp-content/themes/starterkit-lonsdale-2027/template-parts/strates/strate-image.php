<?php
$args = get_template_args();
$image = $args['image'] ?? null;
$has_image = (is_scalar($image) || $image instanceof Stringable)
    && trim((string) $image) !== '';
if (!$has_image) return;
?>

<section <?= options('strate strate-image', $args) ?>>
    <?php component('picture', [
        'image' => $image,
        'classes' => 'strate-image-media',
    ]); ?>
</section>
