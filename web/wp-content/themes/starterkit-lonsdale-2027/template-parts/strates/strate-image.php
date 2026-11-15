<?php
$args = get_template_args();
$image = $args['image'] ?? null;
$has_image = (is_scalar($image) || $image instanceof Stringable)
    && trim((string) $image) !== '';
if (!$has_image) return;
$fixed_height = in_array($args['fixed_height'] ?? false, [true, 1, '1'], true);
?>

<section <?= options(['strate', 'strate-image', $fixed_height ? 'has-fixed-height' : ''], $args) ?>>
    <?php component('picture', [
        'image' => $image,
        'cover' => $fixed_height,
    ]); ?>
</section>
