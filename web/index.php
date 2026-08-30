<?php
/* wp-static-cache:start */
// WP Static : sert la page statique (si elle existe) AVANT de charger WordPress.
// Ajouté/retiré automatiquement par l'extension — ne pas modifier à la main.
// Logique : wp-content/plugins/wp-static/pre-wp-cache.php
$wpsc_service = __DIR__ . '/' . 'wp-content/plugins/wp-static/pre-wp-cache.php';
if (is_file($wpsc_service)) {
    require_once $wpsc_service;
    if (function_exists('wp_static_serve_pre_wp')) {
        wp_static_serve_pre_wp(__DIR__ . '/' . 'wp-content/static-pages', true);
    }
}
/* wp-static-cache:end */

/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';
