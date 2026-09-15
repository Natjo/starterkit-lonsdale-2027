<?php

// remove type="text/javascript" and type="text/css" attributes
add_filter('style_loader_tag', 'remove_type_attr', 10, 2);
add_filter('script_loader_tag', 'remove_type_attr', 10, 2);
function remove_type_attr($tag, $handle)
{
    return preg_replace("/type=['\"]text\/(javascript|css)['\"]/", '', $tag);
}

// dequeue block & WPML styles
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('wpml-tm-admin-bar');
    wp_dequeue_style('classic-theme-styles');

    if (class_exists('WPML\BlockEditor\Loader')) {
        wp_deregister_style(constant('WPML\BlockEditor\Loader::SCRIPT_NAME'));
        wp_dequeue_style('wpml-tm-admin-bar-css');
    }
}, 100);

// remove jQuery from front
add_filter('wp_default_scripts', function (&$scripts) {
    if (!is_admin()) {
        $scripts->remove('jquery');
        $scripts->add('jquery', false, array('jquery-core'), '1.4.1');
    }
});

// remove emoji
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

/**
 * Types de contenus volontairement désactivés dans le back-office du projet.
 *
 * @return list<string>
 */
function theme_hidden_post_types(): array
{
    $native_posts_enabled = defined('THEME_ENABLE_NATIVE_POSTS') && THEME_ENABLE_NATIVE_POSTS;

    return $native_posts_enabled ? [] : ['post'];
}

// Masque les types désactivés dans le menu latéral.
add_action('admin_menu', function () {
    foreach (theme_hidden_post_types() as $post_type) {
        $menu_slug = $post_type === 'post'
            ? 'edit.php'
            : 'edit.php?post_type=' . rawurlencode($post_type);

        remove_menu_page($menu_slug);
    }
});

add_action('admin_bar_menu', function ($wp_admin_bar) {
    foreach (theme_hidden_post_types() as $post_type) {
        $wp_admin_bar->remove_node('new-' . $post_type);
    }
}, 999);

// Masque également leurs panneaux dans Apparence > Menus.
add_filter('nav_menu_meta_box_object', function ($post_type) {
    $name = is_object($post_type) && isset($post_type->name)
        ? (string) $post_type->name
        : '';

    return in_array($name, theme_hidden_post_types(), true) ? false : $post_type;
});

add_action('wp_dashboard_setup', function () {
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
}, 999);

// Le contenu éditorial vient exclusivement des champs ACF.
function remove_native_content_editors(): void
{
    $post_types = ['page', 'news', 'project', 'expertise', 'team', 'testimonial'];
    $native_posts_enabled = defined('THEME_ENABLE_NATIVE_POSTS') && THEME_ENABLE_NATIVE_POSTS;
    if (!$native_posts_enabled) {
        $post_types[] = 'post';
    }

    foreach ($post_types as $post_type) {
        remove_post_type_support($post_type, 'editor');
    }

    if (!$native_posts_enabled) {
        remove_post_type_support('post', 'excerpt');
    }
}
add_action('init', 'remove_native_content_editors', 100);

// no image compression
add_filter('jpeg_quality', function ($arg) {
    return -1;
});
add_filter('wp_editor_set_quality', function ($arg) {
    return -1;
});
