<?php
$args = get_template_args();
$source = $args['video'] ?? null;
$has_source = (is_scalar($source) || $source instanceof Stringable)
    && trim((string) $source) !== '';
if (!$has_source) return;
?>

<section <?= options('strate strate-video', $args) ?>>
    <?php component('video', [
        'url' => $source,
        'title' => $args['video_title'] ?? '',
        'poster' => $args['poster'] ?? null,
    ]); ?>
</section>
