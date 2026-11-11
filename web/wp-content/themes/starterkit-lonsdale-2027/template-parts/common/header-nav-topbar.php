<?php
require_once __DIR__ . '/../../inc/front/header-nav-walker.php';
$location = 'menu-header';
$nav_label = __('Navigation principale', 'theme');
$toggle_label = __('Menu', 'theme');
$site_name = get_bloginfo('name');
$has_menu = has_nav_menu($location);
?>

<header id="header-nav-topbar" role="banner">
        <?php if (has_nav_menu('menu-header-utility')) : ?>
            <nav class="header-nav-utility" aria-label="<?= esc_attr(__('Navigation utilitaire', 'theme')) ?>">
                <?php wp_nav_menu([
                    'theme_location' => 'menu-header-utility',
                    'container' => false,
                    'depth' => 1,
                    'menu_class' => 'header-nav-menu',
                    'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                    'walker' => new Theme_Header_Nav_Walker(),
                    'fallback_cb' => false,
                ]); ?>
            </nav>
        <?php endif; ?>

    <a class="header-nav-logo" href="<?= esc_url(home_url('/')) ?>" aria-label="<?= esc_attr($site_name) ?>">
        <?php component('icon', ['name' => 'logo', 'width' => 54, 'height' => 55]); ?>
        <span class="sr-only"><?= esc_html($site_name) ?></span>
    </a>

    <button class="btn-nav" type="button" aria-expanded="false" aria-controls="nav">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span class="sr-only"><?= esc_html($toggle_label) ?></span>
    </button>

    <nav id="nav" aria-label="<?= esc_attr($nav_label) ?>">
        <?php if ($has_menu) : ?>
            <?php
            wp_nav_menu([
                'theme_location' => $location,
                'container' => false,
                'depth' => 1,
                'menu_class' => 'header-nav-menu',
                'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                'walker' => new Theme_Header_Nav_Walker(),
                'fallback_cb' => false,
            ]);
            ?>
        <?php endif; ?>

        <?php if (!defined('THEME_ENABLE_SEARCH') || THEME_ENABLE_SEARCH) : ?>
            <?php component('search', [
                'value' => get_search_query(),
                'action' => home_url('/'),
                'label' => __('Rechercher sur le site', 'theme'),
                'placeholder' => __('Votre recherche', 'theme'),
                'submit_label' => __('Rechercher', 'theme'),
                'classes' => 'header-search',
            ]); ?>
        <?php endif; ?>
    </nav>
</header>
