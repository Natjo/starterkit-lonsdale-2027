<?php

add_action('after_setup_theme', function () {
    register_nav_menus(array(
        'menu-header' => 'Menu Principal',
        'menu-header-cta' => 'Action du header',
        'menu-header-utility' => 'Navigation utilitaire',
        'menu-header-tools' => 'Compte et panier',
        'menu-header-links' => 'Liens du header',
        'menu-footer' => 'Menu Footer',
        'menu-legal' => 'Menu légal',
        'menu-secondary' => 'Menu secondaire',
    ));

    show_admin_bar(false);

    load_theme_textdomain('theme', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support(
        'html5',
        array(
            'search-form',
            'gallery',
            'caption',
        ),
        'post-thumbnails'
    );
});
