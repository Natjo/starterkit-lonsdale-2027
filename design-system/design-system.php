<?php
/**
 * Template Name: Design system composants
 * Template Post Type: page
 */

require_once __DIR__ . '/readme.php';
$design_system_families = require __DIR__ . '/families.php';
$design_system_family = isset($design_system_family) && isset($design_system_families[$design_system_family]) ? $design_system_family : 'components';
$family = $design_system_families[$design_system_family];
$show_readme = in_array($design_system_family, ['components', 'cards'], true);
if ($design_system_family === 'strates') require_once __DIR__ . '/strate-options.php';
$design_system_css_path = __DIR__ . '/design-system.css';
wp_enqueue_style('theme-design-system', forge_ds_asset('design-system.css'), [], (string) filemtime($design_system_css_path));

$image_desktop = rtrim(THEME_ASSETS, '/') . '/img/placeholders/429-1240x800.jpg';
$image_mobile = $image_desktop;
$design_system_options = [
    ['value' => 'paris', 'label' => 'Paris', 'alt' => 'Île-de-France'],
    ['value' => 'lyon', 'label' => 'Lyon', 'alt' => 'Rhône'],
    ['value' => 'nantes', 'label' => 'Nantes', 'alt' => 'Loire-Atlantique'],
];
$design_system_news = [
    [
        'source' => 'Lonsdale',
        'date' => '20 juillet 2026',
        'datetime' => '2026-07-20',
        'title' => 'Une actualité présentée dans une carte',
        'text' => '<p>Un contenu factice pour vérifier le composant dans une liste.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Lire l’actualité',
        ],
    ],
    [
        'source' => 'Design system',
        'date' => '18 juillet 2026',
        'datetime' => '2026-07-18',
        'title' => 'Un deuxième exemple de contenu',
        'text' => '<p>La répétition permet de contrôler la grille et les espacements.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Voir le détail',
        ],
    ],
    [
        'source' => 'Design system',
        'date' => '18 juillet 2026',
        'datetime' => '2026-07-18',
        'title' => 'Un deuxième exemple de contenu',
        'text' => '<p>La répétition permet de contrôler la grille et les espacements.</p>',
        'link' => [
            'url' => '#component-list',
            'title' => 'Voir le détail',
        ],
    ],
];
$catalogue = require __DIR__ . '/catalogue.php';
$is_project_design_system = is_file(__DIR__ . '/project.json');
if ($is_project_design_system) require __DIR__ . '/catalogue-project.php';
if ($design_system_family === 'modules') {
    $modules_path = __DIR__ . '/docs/catalogue.json';
    $module_catalogue = is_readable($modules_path) ? json_decode(file_get_contents($modules_path), true) : [];
    $catalogue['modules'] = [];
    foreach (is_array($module_catalogue['modules'] ?? null) ? $module_catalogue['modules'] : [] as $slug => $definition) {
        if (is_string($slug) && preg_match('/^[a-z0-9_-]+$/i', $slug) && is_array($definition)) $catalogue['modules'][$slug] = $definition;
    }
}
$design_system_sections = match ($design_system_family) {
    'components' => $is_project_design_system ? array_combine(array_keys($catalogue['components']), array_keys($catalogue['components'])) : require __DIR__ . '/components-index.php',
    'modules' => array_map(static fn ($definition) => $definition['name'], $catalogue['modules']),
    'styles' => ['colors' => 'Couleurs', 'backgrounds' => 'Backgrounds', 'typography' => 'Typographie', 'icons' => 'Icônes', 'spacing' => 'Espacements', 'surfaces' => 'Surfaces', 'layout' => 'Layout', 'breakpoints' => 'Breakpoints'],
    default => array_combine(array_keys($catalogue[$design_system_family]), array_keys($catalogue[$design_system_family])),
};
if ($design_system_family === 'heros') {
    foreach (array_keys($design_system_sections) as $name) theme_queue_hero_critical($name);
}
get_header();
?>
<main class="design-system<?= match ($design_system_family) { 'heros' => ' design-system--heros', 'strates' => ' design-system--strates', default => '' } ?>" id="main-content" data-forge-design-system>
    <?php require __DIR__ . '/intro.php'; ?>
    <nav class="design-system-nav design-system-nav--search" aria-label="Sommaire des exemples">
        <input class="design-system-search" type="search" data-design-system-search aria-label="Rechercher un exemple" placeholder="Rechercher" autocomplete="off">
        <ul role="list">
            <?php foreach ($design_system_sections as $slug => $label) :
                $prefix = match ($design_system_family) { 'components' => 'component-', 'cards' => 'card-', 'strates' => 'strate-', 'heros' => 'hero-', 'styles' => 'style-', 'common' => 'common-', 'modules' => 'module-' };
            ?>
                <li><a href="#<?= esc_attr($prefix . $slug) ?>"><?= esc_html($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <p class="design-system-empty" data-design-system-empty role="status" hidden>Aucun exemple trouvé.</p>
    <?php if ($design_system_family === 'styles') : ?>
        <?php require __DIR__ . '/demos-styles.php'; ?>
    <?php elseif ($design_system_family === 'modules') : ?>
        <?php require __DIR__ . '/demos-modules.php'; ?>
    <?php elseif ($design_system_family === 'components') : ?>
        <?php require __DIR__ . '/demos-components.php'; ?>
        <?php if ($is_project_design_system) require __DIR__ . '/demos-components-project.php'; ?>
    <?php else : ?>
        <div class="st-components">
            <?php foreach ($catalogue[$design_system_family] as $name => $payload) :
                $module = $prefix . $name;
                if ($design_system_family === 'strates') $payload = theme_design_system_strate_payload($name, $payload);
            ?>
                <section class="design-system-section <?= $show_readme ? 'design-system-section--component' : 'design-system-section--full' ?>" id="<?= esc_attr($module) ?>">
                    <div class="design-system-demo">
                        <?php if ($design_system_family === 'strates') : ?>
                        <div class="design-system-strate-toolbar" data-design-system-strate-tools>
                        <?php endif; ?>
                        <h2 class="design-system-section-title<?= $design_system_family === 'cards' ? ' design-system-section-title--version' : '' ?>">
                            <code><?= esc_html($module) ?></code>
                            <?php if ($design_system_family === 'cards') : ?>
                                <?php $card_columns = theme_design_system_card_columns($name); ?>
                                <label class="design-system-card-columns">
                                    <span>Colonnes</span>
                                    <input type="range" min="1" max="3" step="1" value="<?= esc_attr($card_columns) ?>" data-card-columns aria-label="Nombre de colonnes pour <?= esc_attr($module) ?>">
                                    <output><?= esc_html($card_columns) ?></output>
                                </label>
                            <?php endif; ?>
                            <?php if (in_array($design_system_family, ['heros', 'cards'], true)) : ?><span class="design-system-section-version"><?= esc_html(theme_design_system_version($name, $design_system_family)) ?></span><?php endif; ?>
                        </h2>
                        <?php if ($design_system_family === 'strates') : ?>
                            <div class="design-system-strate-menu" role="group" aria-label="Options et documentation de <?= esc_attr($module) ?>">
                                <?php foreach (['options' => 'Options', 'readme' => 'README'] as $panel => $label) : ?>
                                    <button type="button" id="toggle-panel-<?= esc_attr($panel . '-' . $module) ?>" data-design-system-panel-toggle aria-expanded="false" aria-controls="panel-<?= esc_attr($panel . '-' . $module) ?>"><?= esc_html($label) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php foreach (['options', 'readme'] as $panel) : ?>
                                <div class="design-system-strate-panel" id="panel-<?= esc_attr($panel . '-' . $module) ?>" data-design-system-panel="<?= esc_attr($panel) ?>" data-lenis-prevent role="region" aria-labelledby="toggle-panel-<?= esc_attr($panel . '-' . $module) ?>" tabindex="-1" hidden>
                                    <?php if ($panel === 'options') : ?>
                                        <?php theme_design_system_strate_options($module, $payload); ?>
                                    <?php else : ?>
                                        <div class="design-system-readme"><?= theme_design_system_readme($module, $design_system_family) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <span class="design-system-section-version"><?= esc_html(theme_design_system_version($name, $design_system_family)) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="<?= match ($design_system_family) { 'heros' => 'design-system-hero-preview', 'strates' => 'design-system-strate-preview', 'cards' => 'design-system-cards-preview', default => 'design-system-preview design-system-preview--layout' } ?>">
                            <?php
                            switch ($design_system_family) {
                                case 'cards':
                                    foreach (require __DIR__ . '/card-examples.php' as $example) card($name, $example);
                                    break;
                                case 'strates': strate($name, $payload); break;
                                case 'heros': hero($name, $payload); break;
                            }
                            ?>
                        </div>
                    </div>
                    <?php if ($show_readme) : ?>
                    <aside class="design-system-readme" aria-labelledby="readme-<?= esc_attr($module) ?>">
                        <h3 class="design-system-readme-title" id="readme-<?= esc_attr($module) ?>">README</h3>
                        <?= theme_design_system_readme($module, $design_system_family) ?>
                    </aside>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php require __DIR__ . '/breakpoint-indicator.php'; ?>
</main>
<?php get_footer(); ?>
