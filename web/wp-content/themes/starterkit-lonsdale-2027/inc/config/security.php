<?php

// disable feed
add_action('do_feed', 'theme_disable_feeds', 1);
add_action('do_feed_rdf', 'theme_disable_feeds', 1);
add_action('do_feed_rss', 'theme_disable_feeds', 1);
add_action('do_feed_rss2', 'theme_disable_feeds', 1);
add_action('do_feed_atom', 'theme_disable_feeds', 1);
add_action('do_feed_rss2_comments', 'theme_disable_feeds', 1);
add_action('do_feed_atom_comments', 'theme_disable_feeds', 1);

function theme_disable_feeds(): void
{
    wp_die(__('Non disponible, <a href="' . esc_url(home_url('/')) . '">accueil</a>'), '', ['response' => 404]);
}

// remove wp version
remove_action('wp_head', 'wp_generator');


// remove version from rss
add_filter('the_generator', '__return_empty_string');

// yoast remove version number
add_filter('wpseo_hide_version', '__return_true');

add_filter('wpseo_debug_markers', '__return_false');

// change errors hints
function theme_login_error_message(): string
{
    return 'Erreur d\'authentification !';
}
add_filter('login_errors', 'theme_login_error_message');

// disable xmlrpc
add_filter('xmlrpc_methods', function () {
    return [];
}, PHP_INT_MAX);

// Disable X-Pingback to header
add_filter('wp_headers', 'theme_remove_pingback_header');
function theme_remove_pingback_header(array $headers): array
{
    unset($headers['X-Pingback']);

    return $headers;
}

// REST privée pour les visiteurs, à l'exception de la route de maintenance authentifiée.
add_filter('rest_pre_dispatch', function ($result, WP_REST_Server $server, WP_REST_Request $request) {
    if ($result !== null || is_user_logged_in()) {
        return $result;
    }

    $allowed_routes = [
        '/starter/v1/maintenance',
    ];

    if (in_array($request->get_route(), $allowed_routes, true)) {
        return $result;
    }

    return new WP_Error(
        'rest_forbidden',
        'Non autorisé.',
        ['status' => rest_authorization_required_code()]
    );
}, 10, 3);

// SVG sanitization on upload
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('manage_options')) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
});

add_filter('wp_handle_upload_prefilter', function ($file) {
    if ($file['type'] !== 'image/svg+xml') {
        return $file;
    }

    $sanitizer = new \enshrined\svgSanitize\Sanitizer();
    $dirty = file_get_contents($file['tmp_name']);
    $clean = $sanitizer->sanitize($dirty);

    if ($clean === false) {
        $file['error'] = 'Le fichier SVG est invalide ou contient du contenu non autorisé.';
        return $file;
    }

    file_put_contents($file['tmp_name'], $clean);

    return $file;
});

// remove link rsd
remove_action('wp_head', 'rsd_link');

remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('template_redirect', 'rest_output_link_header', 11);
