<?php
/**
 * Template Name: Styleguide composants
 * Template Post Type: page
 */

require_once __DIR__ . '/readme.php';
$styleguide_families = [
    'styles' => ['label' => 'Styles', 'slug' => 'styles', 'template' => 'styles.php'],
    'components' => ['label' => 'Composants', 'slug' => 'components', 'template' => 'styleguide.php'],
    'cards' => ['label' => 'Cards', 'slug' => 'cards', 'template' => 'cards.php'],
    'strates' => ['label' => 'Strates', 'slug' => 'strates', 'template' => 'strates.php'],
    'heros' => ['label' => 'Heroes', 'slug' => 'heros', 'template' => 'heros.php'],
];
$styleguide_family = isset($styleguide_family) && isset($styleguide_families[$styleguide_family]) ? $styleguide_family : 'components';
$family = $styleguide_families[$styleguide_family];
$show_readme = in_array($styleguide_family, ['components', 'cards'], true);
if ($styleguide_family === 'strates') require_once __DIR__ . '/strate-options.php';
$styleguide_css_path = __DIR__ . '/styleguide.css';
wp_enqueue_style('theme-styleguide', get_template_directory_uri() . '/inc/front/styleguide/styleguide.css', [], (string) filemtime($styleguide_css_path));

$image_desktop = rtrim(THEME_URL, '/') . '/inc/front/styleguide/429-1240x800.jpg';
$image_mobile = rtrim(THEME_URL, '/') . '/inc/front/styleguide/429-1240x800.jpg';
$styleguide_options = [
    ['value' => 'paris', 'label' => 'Paris', 'alt' => 'Île-de-France'],
    ['value' => 'lyon', 'label' => 'Lyon', 'alt' => 'Rhône'],
    ['value' => 'nantes', 'label' => 'Nantes', 'alt' => 'Loire-Atlantique'],
];
$styleguide_news = [
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
$styleguide_sections = match ($styleguide_family) {
    'components' => require __DIR__ . '/components-index.php',
    'styles' => ['colors' => 'Couleurs', 'typography' => 'Typographie', 'spacing' => 'Espacements', 'surfaces' => 'Surfaces', 'layout' => 'Layout', 'breakpoints' => 'Breakpoints'],
    default => array_combine(array_keys($catalogue[$styleguide_family]), array_keys($catalogue[$styleguide_family])),
};
if ($styleguide_family === 'heros') {
    foreach (array_keys($styleguide_sections) as $name) theme_queue_hero_critical($name);
}
$breakpoints_path = get_template_directory() . '/assets/styleguide/breakpoints.json';
$styleguide_breakpoints = is_readable($breakpoints_path) ? json_decode(file_get_contents($breakpoints_path), true) : [];
$styleguide_breakpoints = is_array($styleguide_breakpoints) ? $styleguide_breakpoints : [];
get_header();
?>
<main class="styleguide<?= match ($styleguide_family) { 'heros' => ' styleguide--heros', 'strates' => ' styleguide--strates', default => '' } ?>" id="main-content" data-module="pages/styleguide">
    <header class="styleguide-intro">
        <p class="styleguide-kicker"><span>Design system</span><span><?= count($styleguide_sections) ?> exemples</span></p>
        <h1>Styleguide — <?= esc_html($family['label']) ?></h1>
        <p class="styleguide-lead">Démonstrations et documentation du catalogue <?= esc_html($family['label']) ?>.</p>
        <nav class="styleguide-families" aria-label="Rubriques du Styleguide">
            <?php foreach ($styleguide_families as $key => $entry) :
                $page = get_page_by_path($entry['slug']);
                if (!$page) continue;
            ?>
                <a href="<?= esc_url(get_permalink($page)) ?>"<?= $key === $styleguide_family ? ' aria-current="page"' : '' ?>><?= esc_html($entry['label']) ?></a>
            <?php endforeach; ?>
        </nav>
    </header>
    <nav class="styleguide-nav styleguide-nav--search" aria-label="Sommaire des exemples">
        <input class="styleguide-search" type="search" data-styleguide-search aria-label="Rechercher un exemple" placeholder="Rechercher" autocomplete="off">
        <ul role="list">
            <?php foreach ($styleguide_sections as $slug => $label) :
                $prefix = match ($styleguide_family) { 'components' => 'component-', 'cards' => 'card-', 'strates' => 'strate-', 'heros' => 'hero-', 'styles' => 'style-' };
            ?>
                <li><a href="#<?= esc_attr($prefix . $slug) ?>"><?= esc_html($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <p class="styleguide-empty" data-styleguide-empty role="status" hidden>Aucun exemple trouvé.</p>
    <?php if ($styleguide_family === 'styles') : ?>
        <?php require __DIR__ . '/demos-styles.php'; ?>
    <?php elseif ($styleguide_family === 'components') : ?>
        <?php require __DIR__ . '/demos-components.php'; ?>
    <?php else : ?>
        <div class="st-components">
            <?php foreach ($catalogue[$styleguide_family] as $name => $payload) :
                $module = $prefix . $name;
            ?>
                <section class="styleguide-section <?= $show_readme ? 'styleguide-section--component' : 'styleguide-section--full' ?>" id="<?= esc_attr($module) ?>">
                    <div class="styleguide-demo">
                        <?php if ($styleguide_family === 'strates') : ?>
                        <div class="styleguide-strate-toolbar" data-styleguide-strate-tools>
                        <?php endif; ?>
                        <h2 class="styleguide-section-title"><code><?= esc_html($module) ?></code></h2>
                        <?php if ($styleguide_family === 'strates') : ?>
                            <div class="styleguide-strate-menu" role="group" aria-label="Options et documentation de <?= esc_attr($module) ?>">
                                <?php foreach (['options' => 'Options', 'readme' => 'README'] as $panel => $label) : ?>
                                    <button type="button" id="toggle-panel-<?= esc_attr($panel . '-' . $module) ?>" data-styleguide-panel-toggle aria-expanded="false" aria-controls="panel-<?= esc_attr($panel . '-' . $module) ?>"><?= esc_html($label) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php foreach (['options', 'readme'] as $panel) : ?>
                                <div class="styleguide-strate-panel" id="panel-<?= esc_attr($panel . '-' . $module) ?>" data-styleguide-panel="<?= esc_attr($panel) ?>" data-lenis-prevent role="region" aria-labelledby="toggle-panel-<?= esc_attr($panel . '-' . $module) ?>" tabindex="-1" hidden>
                                    <?php if ($panel === 'options') : ?>
                                        <?php theme_styleguide_strate_options($module, $payload); ?>
                                    <?php else : ?>
                                        <div class="styleguide-readme"><?= theme_styleguide_readme($module, $styleguide_family) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div class="<?= match ($styleguide_family) { 'heros' => 'styleguide-hero-preview', 'strates' => 'styleguide-strate-preview', default => 'styleguide-preview styleguide-preview--layout' } ?>">
                            <?php
                            switch ($styleguide_family) {
                                case 'cards': card($name, $payload); break;
                                case 'strates': strate($name, $payload); break;
                                case 'heros': hero($name, $payload); break;
                            }
                            ?>
                        </div>
                        <?php if (str_starts_with($name, 'pushes')) : ?>
                            <p class="styleguide-note">Cette démonstration utilise les contenus News publiés du site.</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($show_readme) : ?>
                    <aside class="styleguide-readme" aria-labelledby="readme-<?= esc_attr($module) ?>">
                        <h3 class="styleguide-readme-title" id="readme-<?= esc_attr($module) ?>">README</h3>
                        <?= theme_styleguide_readme($module, $styleguide_family) ?>
                    </aside>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($styleguide_breakpoints !== []) : ?>
        <aside class="styleguide-breakpoint-indicator" aria-label="Breakpoint actuel"
               data-styleguide-breakpoint data-breakpoints="<?= esc_attr(wp_json_encode($styleguide_breakpoints)) ?>" hidden>
            <strong data-breakpoint-name></strong>
            <span data-breakpoint-range></span>
            <span data-styleguide-viewport></span>
        </aside>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
