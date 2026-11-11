        <?php if (has_nav_menu('menu-secondary')) wp_nav_menu([
            'theme_location' => 'menu-secondary', 'container' => false, 'depth' => 1,
            'menu_class' => 'menu-secondary header-nav-menu',
            'items_wrap' => '<ul class="%2$s">%3$s</ul>',
            'walker' => new Theme_Header_Nav_Walker(), 'fallback_cb' => false,
        ]); ?>
