<?php

add_action('after_setup_theme', function () {
    remove_post_type_support('post', 'comments');
    remove_post_type_support('page', 'comments');
    remove_post_type_support('post', 'trackbacks');
    remove_post_type_support('page', 'trackbacks');
});

add_action('init', function () {
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_menu('comments');
}, 999);

add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }
});

add_action('wp_dashboard_setup', function () {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
});
