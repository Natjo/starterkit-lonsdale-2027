        <?php if (has_nav_menu('menu-header-utility')) wp_nav_menu([
            'theme_location' => 'menu-header-utility', 'container' => false, 'depth' => 1,
            'menu_class' => 'header-topbar header-nav-menu',
            'items_wrap' => '<ul class="%2$s">%3$s</ul>',
            'walker' => new Theme_Header_Nav_Walker(), 'fallback_cb' => false,
        ]); ?>
