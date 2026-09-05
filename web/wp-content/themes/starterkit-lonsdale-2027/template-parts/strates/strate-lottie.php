<?php
$args = get_template_args();
$animation_url = get_template_directory_uri() . '/assets/lottie/animation.json';
?>

<section <?= options('strate strate-lottie', $args) ?>>
    <?php component('lottie', [
        'src' => $animation_url,
        'autoplay' => true,
        'loop' => true,
        'width' => 1920,
        'height' => 1080,
    ]); ?>
</section>
