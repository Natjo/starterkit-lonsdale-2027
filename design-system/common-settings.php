<?php
/** Shared validated parameters for the Common page and its standalone test route. */
$items = require __DIR__ . '/common-catalogue.php';
$selected_common = '';
if (isset($_GET['forge_common'])) {
    $value = is_string($_GET['forge_common']) ? wp_unslash($_GET['forge_common']) : '';
    if (!isset($items[$value])) { status_header(404); exit('Aperçu introuvable.'); }
    $selected_common = $value;
}
$header = preg_match('/^header(-|$)/', $selected_common) ? $selected_common : 'header-nav';
$footers = array_filter($items, static fn ($name) => preg_match('/^footer(-|$)/', $name), ARRAY_FILTER_USE_KEY);
$footer = isset($footers[$selected_common]) ? $selected_common : (isset($footers['footer']) ? 'footer' : (array_key_first($footers) ?? ''));
if (isset($_GET['forge_footer'])) {
    $value = is_string($_GET['forge_footer']) ? wp_unslash($_GET['forge_footer']) : '';
    if (!isset($footers[$value])) { status_header(404); exit('Aperçu introuvable.'); }
    $footer = $value;
}
$configurable = $header === 'header-nav' && isset($items['header-nav']['headerConfiguration']['version'], $items['header-nav-dropdown'], $items['header-nav-mega']) && is_file(get_template_directory() . '/template-parts/common/header-parts/render.php');
$header_config = ['layout' => 'default', 'navigation' => 'simple', 'search' => false, 'buttons' => false, 'links' => false];
foreach (['navigation' => ['simple','dropdown','mega','mega-columns']] as $key => $allowed) {
    $value = $_GET['header_' . $key] ?? $header_config[$key];
    if (!is_string($value) || !in_array($value, $allowed, true)) { status_header(404); exit('Configuration du header invalide.'); }
    $header_config[$key] = $value;
}
foreach (['search','buttons','links'] as $key) $header_config[$key] = ($_GET['header_' . $key] ?? '') === '1';

$footer_configurable = $footer === 'footer' && isset($items['footer']['footerConfiguration']['version']) && is_file(get_template_directory() . '/template-parts/common/footer-parts/render.php');
$footer_config = ['type'=>'simple','socials'=>false,'secondaryMenu'=>false,'search'=>false,'newsletter'=>false,'copyright'=>!isset($_GET['footer_config']),'text'=>false];
$type = $_GET['footer_type'] ?? 'simple';
if (!is_string($type) || !in_array($type, ['simple','columns'], true)) { status_header(404); exit('Configuration du footer invalide.'); }
$footer_config['type'] = $type;
foreach (['socials','secondaryMenu','copyright','text','newsletter','search'] as $key) {
    if (isset($_GET['footer_' . $key])) $footer_config[$key] = $_GET['footer_' . $key] === '1';
}
if ($type === 'simple') $footer_config['text'] = false;
if ($type === 'simple') { $footer_config['search'] = false; $footer_config['newsletter'] = false; }
