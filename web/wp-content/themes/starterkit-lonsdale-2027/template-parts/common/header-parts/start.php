<?php
$site_name = get_bloginfo('name');
?>
<header id="<?= esc_attr($header_id) ?>" class="header-layout-<?= esc_attr($header_layout) ?> header-navigation-<?= esc_attr($header_navigation ?? 'simple') ?>" role="banner" data-submenu-label="<?= esc_attr(__('Afficher le sous-menu', 'theme')) ?>">
