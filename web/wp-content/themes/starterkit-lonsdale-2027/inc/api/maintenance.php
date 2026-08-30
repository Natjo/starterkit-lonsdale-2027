<?php

add_action('rest_api_init', function () {
    register_rest_route('starter/v1', '/maintenance', [
        'methods'             => 'GET',
        'callback'            => 'starter_maintenance_endpoint',
        'permission_callback' => 'starter_maintenance_permission',
    ]);
});

function starter_maintenance_permission(WP_REST_Request $request): bool
{
    if (current_user_can('manage_options')) {
        return true;
    }

    if (!defined('MAINTENANCE_KEY') || empty(MAINTENANCE_KEY)) {
        return false;
    }

    $token = $request->get_header('X-Maintenance-Key');

    return $token === MAINTENANCE_KEY;
}

function starter_maintenance_endpoint(): WP_REST_Response
{
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if (!function_exists('get_plugin_updates')) {
        require_once ABSPATH . 'wp-admin/includes/update.php';
    }

    wp_update_plugins();
    wp_update_themes();
    wp_version_check();

    $plugins        = get_plugins();
    $plugin_updates = get_plugin_updates();
    $active_plugins = get_option('active_plugins', []);

    $plugins_data = [];
    foreach ($plugins as $file => $plugin) {
        $update_available = isset($plugin_updates[$file]);
        $plugins_data[]   = [
            'name'             => $plugin['Name'],
            'slug'             => dirname($file),
            'version'          => $plugin['Version'],
            'active'           => in_array($file, $active_plugins, true),
            'update_available' => $update_available,
            'new_version'      => $update_available ? $plugin_updates[$file]->update->new_version : null,
        ];
    }

    global $wp_version;
    $core_updates       = get_core_updates();
    $core_update_needed = false;
    $core_new_version   = null;

    if (is_array($core_updates) && !empty($core_updates)) {
        $latest = $core_updates[0];
        if (isset($latest->response) && $latest->response === 'upgrade') {
            $core_update_needed = true;
            $core_new_version   = $latest->current;
        }
    }

    return new WP_REST_Response([
        'wordpress' => [
            'version'          => $wp_version,
            'update_available' => $core_update_needed,
            'new_version'      => $core_new_version,
        ],
        'plugins' => $plugins_data,
    ], 200);
}
