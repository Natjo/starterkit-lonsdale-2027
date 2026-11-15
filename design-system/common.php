<?php
/** Template Name: Design system common */
require __DIR__ . '/common-settings.php';
if (isset($_GET['common_fragment'])) {
    $fragment = $_GET['common_fragment'];
    if (!is_string($fragment) || !in_array($fragment, ['header', 'footer'], true)) { status_header(404); exit('Aperçu introuvable.'); }
    nocache_headers();
    header('Content-Type: text/html; charset=UTF-8');
    require __DIR__ . '/common-' . $fragment . '.php';
    return;
}
$design_system_families = require __DIR__ . '/families.php';
$design_system_family = 'common';
$family = $design_system_families[$design_system_family];
$design_system_sections = ['common' => 'Page de démonstration'];
$is_project_design_system = is_file(__DIR__ . '/project.json');
$base = forge_ds_asset('');
wp_enqueue_style('theme-design-system', $base . 'design-system.css', [], (string) filemtime(__DIR__ . '/design-system.css'));
wp_enqueue_style('forge-common-demo', $base . 'common-demo.css', [], (string) filemtime(__DIR__ . '/common-demo.css'));
// Render first: the selected helpers can register critical/split CSS before wp_head.
ob_start();
require __DIR__ . '/common-page.php';
$demo = ob_get_clean();
ob_start();
require __DIR__ . '/common-controls.php';
$design_system_header_controls = ob_get_clean();
get_header();
?>
<main class="design-system design-system--common" id="main-content" data-forge-design-system>
    <?php require __DIR__ . '/intro.php'; ?>
    <section class="forge-common-site"<?= preg_match('/^header(-|$)/', $selected_common) ? ' id="common-' . esc_attr($selected_common) . '"' : '' ?> data-common-demo aria-label="Page de démonstration Common"><?= $demo ?></section>
    <?php require __DIR__ . '/breakpoint-indicator.php'; ?>
</main>
<?php wp_footer(); ?></body></html>
