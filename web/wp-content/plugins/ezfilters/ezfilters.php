<?php
/**
 * Plugin Name: EZFilters
 * Description: Système de filtres avancé pour les archives WordPress
 * Version: 1.0.0
 * Author: STUDIO DIGITAL by LONSDALE
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/autoload.php';

register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
