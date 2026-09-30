<?php

add_action('after_setup_theme', function () {
    add_image_size('730_auto', 730, 0); // strate text-image
    add_image_size('40_40', 40, 40, true); // component quote
    add_image_size('718_478', 718, 478, true); // card news
});
