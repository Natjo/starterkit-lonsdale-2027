<?php
/** Starter preview; generated sites receive only their selected parts. */
$config = $GLOBALS['theme_footer_preview'] ?? ['type' => 'simple', 'copyright' => true];
$config = is_array($config) ? $config : [];
$footer_type = ($config['type'] ?? '') === 'columns' ? 'columns' : 'simple';
require_once get_template_directory() . '/inc/front/header-nav-walker.php';
if ($footer_type === 'columns') require_once get_template_directory() . '/inc/front/footer-columns-walker.php';
require __DIR__ . '/start.php';
if ($footer_type === 'columns' && ($config['text'] ?? false) === true) require __DIR__ . '/text.php';
if (($config['socials'] ?? false) === true) require __DIR__ . '/socials.php';
require __DIR__ . '/menu-' . $footer_type . '.php';
if ($footer_type === 'simple' && ($config['search'] ?? false) === true) require __DIR__ . '/search.php';
require __DIR__ . '/bottom.php';
if (($config['copyright'] ?? false) === true) require __DIR__ . '/copyright.php';
if (($config['secondaryMenu'] ?? false) === true) require __DIR__ . '/secondary.php';
require __DIR__ . '/end.php';
