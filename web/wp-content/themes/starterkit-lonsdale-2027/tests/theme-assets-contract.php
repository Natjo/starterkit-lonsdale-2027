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

function add_action(mixed ...$args): void
{
}

function add_filter(mixed ...$args): void
{
}

require_once dirname(__DIR__) . '/inc/helpers/theme-assets.php';
require_once dirname(__DIR__) . '/inc/helpers/assets.php';

ob_start();
fontPreload([
    'SequelSansLight',
    'SequelSansBook',
    'SequelSansLight',
    '../SequelSansHeavy',
    'FonteAbsente',
    123,
]);
$fontPreloads = (string) ob_get_clean();

ob_start();
fontPreload(['SequelSansLight']);
$duplicateFontPreload = (string) ob_get_clean();

theme_queue_hero_critical('homepage');
$initialHeroCss = ThemeAssets::getHeroCriticalCssForHead();

if (!str_contains($initialHeroCss, '.hero-homepage')) {
    fwrite(STDERR, "Le CSS critique du hero préscanné doit être rendu dans wp_head.\n");
    exit(1);
}

$heroQueue = new ReflectionProperty(ThemeAssets::class, 'heroCriticalQueue');
$heroQueue->setAccessible(true);
$queuedHeroes = $heroQueue->getValue();
$queuedHeroes['strates/strate-intro/strate-intro'] = true;
$heroQueue->setValue(null, $queuedHeroes);

$lateHeroHtml = ThemeAssets::processBufferedHtml(
    '<!doctype html><html><head><style id="critical-css">initial</style></head><body></body></html>'
);

if (!str_contains($lateHeroHtml, '.strate-intro')) {
    fwrite(STDERR, "Un CSS critique découvert après wp_head doit être ajouté au bloc critique.\n");
    exit(1);
}

theme_queue_template_assets('/var/www/html/wp-content/themes/example/search.php');

if (!in_array('pages/search/search', ThemeAssets::getOnceModules(), true)) {
    fwrite(STDERR, "search.php doit enregistrer automatiquement pages/search/search en mode once.\n");
    exit(1);
}

ob_start();
ThemeAssets::renderOnceStyles();
$templateStyle = (string) ob_get_clean();

ob_start();
ThemeAssets::renderOnceStyles();
$duplicateTemplateStyle = (string) ob_get_clean();

ob_start();
theme_queue_module_assets('pages/404/404');
$moduleOnceStyle = (string) ob_get_clean();

ob_start();
theme_queue_module_assets('pages/404/404');
$duplicateModuleOnceStyle = (string) ob_get_clean();

ThemeAssets::queueSplit('strates/strate-textAnimated/strate-textAnimated');

ob_start();
ThemeAssets::renderSplitLinks();
$textAnimatedLink = (string) ob_get_clean();

ThemeAssets::queueSplit('strates/strate-paralax/strate-paralax');
ob_start();
ThemeAssets::renderSplitLinks();
$paralaxLink = (string) ob_get_clean();
starter_enqueue_preload_image([
    'href' => 'https://example.test/image-718.webp',
    'type' => 'image/webp',
    'imagesrcset' => 'https://example.test/image-300.webp 300w, https://example.test/image-718.webp 718w',
    'imagesizes' => '(max-width: 718px) 100vw, 718px',
]);

$html = ThemeAssets::processBufferedHtml(
    '<!doctype html><html><head><title>Test</title>'
    . $templateStyle
    . '</head><body>'
    . $textAnimatedLink
    . '<main>Contenu</main>'
    . $paralaxLink
    . '</body></html>'
);

$headEnd = strpos($html, '</head>');
$bodyStart = strpos($html, '<body>');
$mainStart = strpos($html, '<main>');
$mainEnd = strpos($html, '</main>');
$search = 'id="once-pages-search-search"';
$textAnimated = 'id="split-strates-strate-textAnimated-strate-textAnimated"';
$paralax = 'id="split-strates-strate-paralax-strate-paralax"';
$failures = [];

if (substr_count($fontPreloads, 'rel="preload"') !== 2) {
    $failures[] = 'Le helper de fontes doit ignorer les fichiers invalides, absents et dupliqués.';
}

if (!str_contains($fontPreloads, 'href="https://example.test/assets/fonts/SequelSansLight.woff2"')) {
    $failures[] = 'Le helper de fontes doit construire l’URL du fichier WOFF2.';
}

if (!str_contains($fontPreloads, 'as="font" type="font/woff2" crossorigin')) {
    $failures[] = 'Le preload de fonte doit exposer ses attributs réseau requis.';
}

if ($duplicateFontPreload !== '') {
    $failures[] = 'Le helper de fontes doit dédupliquer plusieurs appels.';
}

if ($headEnd === false || $bodyStart === false) {
    $failures[] = 'Le document de test est invalide.';
}

if (
    ($searchPosition = strpos($html, $search)) === false
    || ($headEnd !== false && $searchPosition > $headEnd)
) {
    $failures[] = 'Le CSS once du template doit rester dans le head.';
}

if (substr_count($html, $search) !== 1) {
    $failures[] = 'Le CSS once du template doit apparaître exactement une fois.';
}

if ($duplicateTemplateStyle !== '') {
    $failures[] = 'Le CSS once du template doit être dédupliqué.';
}

if (str_contains($templateStyle, '<link')) {
    $failures[] = 'Le CSS once du template doit être injecté inline.';
}

if (!str_contains($moduleOnceStyle, 'id="once-pages-404-404"')) {
    $failures[] = 'Le helper des modules doit injecter un CSS once à son premier rendu.';
}

if ($duplicateModuleOnceStyle !== '') {
    $failures[] = 'Le CSS once d’un module doit être dédupliqué.';
}

foreach ([$textAnimated, $paralax] as $id) {
    $position = strpos($html, $id);

    if ($position === false || ($bodyStart !== false && $position < $bodyStart)) {
        $failures[] = sprintf('Le lien %s doit rester au niveau du module dans le body.', $id);
    }

    if (substr_count($html, $id) !== 1) {
        $failures[] = sprintf('Le lien %s doit apparaître exactement une fois.', $id);
    }
}

if (
    $mainStart !== false
    && ($textAnimatedPosition = strpos($html, $textAnimated)) !== false
    && $textAnimatedPosition > $mainStart
) {
    $failures[] = 'Le CSS de textAnimated doit précéder le premier rendu de la strate.';
}

if (
    $mainEnd !== false
    && ($paralaxPosition = strpos($html, $paralax)) !== false
    && $paralaxPosition < $mainEnd
) {
    $failures[] = 'Le CSS de paralax doit être émis à son point de rendu.';
}

if ($headEnd !== false && str_contains(substr($html, 0, $headEnd), 'id="split-strates-')) {
    $failures[] = 'Les CSS split des modules ne doivent pas être remontés dans le head.';
}

if (!str_contains($html, 'imagesrcset="https://example.test/image-300.webp 300w, https://example.test/image-718.webp 718w"')) {
    $failures[] = 'Le preload responsive doit conserver imagesrcset.';
}

if (!str_contains($html, 'imagesizes="(max-width: 718px) 100vw, 718px"')) {
    $failures[] = 'Le preload responsive doit conserver imagesizes.';
}

if (!str_contains($html, 'rel="preload" as="image" href="https://example.test/image-718.webp" fetchpriority="high"')) {
    $failures[] = 'Le preload image doit demander une priorité réseau haute.';
}

if ($failures !== []) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}

echo "Theme assets contract: OK\n";
