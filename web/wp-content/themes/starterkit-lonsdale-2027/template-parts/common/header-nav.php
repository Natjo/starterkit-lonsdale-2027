<?php
$location = 'menu-header';
$nav_label = __('Navigation principale', 'theme');
$toggle_label = __('Menu', 'theme');
$site_name = get_bloginfo('name');
$has_menu = has_nav_menu($location);
?>

<header id="header-nav" role="banner">
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
                'menu_class' => 'header-nav-menu',
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
