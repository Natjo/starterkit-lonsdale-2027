<?php

declare(strict_types=1);

define('THEME_DIR', dirname(__DIR__) . '/');
define('THEME_ASSETS', 'https://example.test/assets/');

function esc_attr(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function esc_url(mixed $value): string
{
    return esc_attr($value);
}

function esc_url_raw(mixed $value): string
{
    return (string) $value;
}

require_once dirname(__DIR__) . '/inc/helpers/theme-assets.php';

theme_queue_template_assets('/var/www/html/wp-content/themes/example/search.php');

if (!in_array('pages/search', ThemeAssets::getSplitModules(), true)) {
    fwrite(STDERR, "search.php doit enregistrer automatiquement pages/search.\n");
    exit(1);
}

ThemeAssets::queueSplit('strates/strate-textAnimated/strate-textAnimated');

ob_start();
ThemeAssets::renderSplitLinks();
$footerLink = (string) ob_get_clean();

ThemeAssets::queueSplit('strates/strate-paralax/strate-paralax');
starter_enqueue_preload_image([
    'href' => 'https://example.test/image-718.webp',
    'type' => 'image/webp',
    'imagesrcset' => 'https://example.test/image-300.webp 300w, https://example.test/image-718.webp 718w',
    'imagesizes' => '(max-width: 718px) 100vw, 718px',
]);

$html = ThemeAssets::processBufferedHtml(
    '<!doctype html><html><head><title>Test</title></head><body>'
    . $footerLink
    . '<main>Contenu</main></body></html>'
);

$headEnd = strpos($html, '</head>');
$bodyStart = strpos($html, '<body>');
$textAnimated = 'id="split-strates-strate-textAnimated-strate-textAnimated"';
$paralax = 'id="split-strates-strate-paralax-strate-paralax"';
$failures = [];

if ($headEnd === false || $bodyStart === false) {
    $failures[] = 'Le document de test est invalide.';
}

foreach ([$textAnimated, $paralax] as $id) {
    $position = strpos($html, $id);

    if ($position === false || ($headEnd !== false && $position > $headEnd)) {
        $failures[] = sprintf('Le lien %s doit être injecté dans le head.', $id);
    }

    if (substr_count($html, $id) !== 1) {
        $failures[] = sprintf('Le lien %s doit apparaître exactement une fois.', $id);
    }
}

if ($bodyStart !== false && str_contains(substr($html, $bodyStart), 'id="split-')) {
    $failures[] = 'Aucun lien CSS split ne doit rester dans le body.';
}

if (!str_contains($html, 'imagesrcset="https://example.test/image-300.webp 300w, https://example.test/image-718.webp 718w"')) {
    $failures[] = 'Le preload responsive doit conserver imagesrcset.';
}

if (!str_contains($html, 'imagesizes="(max-width: 718px) 100vw, 718px"')) {
    $failures[] = 'Le preload responsive doit conserver imagesizes.';
}

if ($failures !== []) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}

echo "Theme assets contract: OK\n";
