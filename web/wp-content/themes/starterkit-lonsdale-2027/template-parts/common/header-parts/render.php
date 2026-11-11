<?php
/** Catalogue preview only. Generated sites receive the flattened selected parts. */
$config = $GLOBALS['theme_header_preview'] ?? [];
$navigation = in_array($config['navigation'] ?? '', ['simple', 'dropdown', 'mega'], true) ? $config['navigation'] : 'simple';
$header_layout = in_array($config['layout'] ?? '', ['default','centered','minimal','topbar','search','commerce'], true) ? $config['layout'] : 'default';
$header_id = match ($navigation) { 'mega' => 'header-nav-mega', 'dropdown' => 'header-nav-dropdown', default => 'header-nav' };
require_once get_template_directory() . '/inc/front/header-nav-walker.php';
if ($navigation === 'mega') require_once get_template_directory() . '/inc/front/header-mega-nav-walker.php';
require __DIR__ . '/start.php';
if ($header_layout === 'topbar') require __DIR__ . '/topbar.php';
require __DIR__ . '/logo.php';
require __DIR__ . '/navigation-' . $navigation . '.php';
if ($header_layout === 'centered') require __DIR__ . '/secondary.php';
foreach (['search', 'buttons', 'pictos', 'links'] as $option) {
    if (($config[$option] ?? false) === true) require __DIR__ . '/' . $option . '.php';
}
require __DIR__ . '/end.php';
