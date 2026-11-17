<?php
require_once __DIR__ . '/../../inc/front/header-nav-walker.php';
$location = 'menu-header';
$nav_label = __('Navigation principale', 'theme');
$toggle_label = __('Menu', 'theme');
$site_name = get_bloginfo('name');
$has_menu = has_nav_menu($location);
$locations = get_nav_menu_locations();
$action_items = !empty($locations['menu-header-cta']) ? wp_get_nav_menu_items($locations['menu-header-cta']) : [];
$action_items = is_array($action_items) ? $action_items : [];
$action = null;
foreach ($action_items as $item) {
    if ((int) $item->menu_item_parent !== 0) continue;
    $action = ['url' => $item->url, 'title' => $item->title, 'target' => $item->target];
    break;
}

?>

<header id="header-nav-cta" role="banner">
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

        <?php if ($action !== null) component('btn', ['link' => $action, 'classes' => 'btn-solid header-nav-action']); ?>
    </nav>
</header>
