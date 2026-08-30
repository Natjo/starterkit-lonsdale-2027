<?php
$image = rtrim(THEME_ASSETS, '/') . '/img/tmp/hero-2.jpg';
?>

<section
    class="strate strate-paralax"
    data-module="strates/strate-paralax"
    data-context="@visible true">
    <div class="strate-paralax-media" aria-hidden="true">
        <?php component('image', [
            'image' => $image,
            'alt' => '',
            'classes' => 'strate-paralax-image',
        ]); ?>
    </div>

    <div class="strate-content">
        <?php component('title', [
            'title' => 'Une vision qui prend de la hauteur',
            'hx' => 2,
            'classes' => 'title-1 strate-paralax-title',
        ]); ?>
    </div>
</section>
