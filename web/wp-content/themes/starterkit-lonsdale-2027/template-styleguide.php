<?php
/**
 * Template Name: Styleguide
 * Template Post Type: page
 */

$styleguide_css_path = get_template_directory() . '/inc/front/styleguide/styleguide.css';
$styleguide_css_version = is_file($styleguide_css_path) ? (string) filemtime($styleguide_css_path) : null;

wp_enqueue_style(
    'theme-styleguide',
    get_template_directory_uri() . '/inc/front/styleguide/styleguide.css',
    [],
    $styleguide_css_version
);

get_header();

get_template_part('inc/front/styleguide/styleguide');

get_footer();
