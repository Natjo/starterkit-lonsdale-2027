<?php

// disable feed
add_action('do_feed', 'itsme_disable_feed', 1);
add_action('do_feed_rdf', 'itsme_disable_feed', 1);
add_action('do_feed_rss', 'itsme_disable_feed', 1);
add_action('do_feed_rss2', 'itsme_disable_feed', 1);
add_action('do_feed_atom', 'itsme_disable_feed', 1);
add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);

function itsme_disable_feed()
{
    wp_die(__('Non disponible, <a href="' . esc_url(home_url('/')) . '">accueil</a>'));
}

// remove wp version
remove_action('wp_head', 'wp_generator');


// remove version from rss
add_filter('the_generator', '__return_empty_string');

// yoast remove version number
add_filter('wpseo_hide_version', '__return_true');

// Canonical page d'accueil sans slash final (aligné sur l'URL publique, évite duplicate content).
add_filter('wpseo_canonical', function ($canonical) {
    if (!is_front_page() || empty($canonical)) {
        return $canonical;
    }
    return untrailingslashit($canonical);
}, 10, 1);

add_filter('get_canonical_url', function ($canonical_url, $post) {
    if (empty($canonical_url) || !is_object($post)) {
        return $canonical_url;
    }
    $front_id = (int) get_option('page_on_front');
    if ($front_id && (int) $post->ID === $front_id) {
        return untrailingslashit($canonical_url);
    }
    return $canonical_url;
}, 10, 2);

add_filter('wpseo_debug_markers', '__return_false');

// change errors hints
function no_wordpress_errors()
{
    return 'Erreur d\'authentification !';
}
add_filter('login_errors', 'no_wordpress_errors');

// disable xmlrpc
add_filter('xmlrpc_methods', function () {
    return [];
}, PHP_INT_MAX);

// Disable X-Pingback to header
add_filter('wp_headers', 'disable_x_pingback');
function disable_x_pingback($headers)
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
