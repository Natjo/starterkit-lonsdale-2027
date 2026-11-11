        <?php if (has_nav_menu('menu-header')) wp_nav_menu([
            'theme_location' => 'menu-header', 'container' => false,
            'depth' => 2,
            'menu_class' => 'nav-menu header-nav-menu',
            'items_wrap' => '<ul class="%2$s">%3$s</ul>',
            'walker' => new Theme_Mega_Nav_Walker(), 'fallback_cb' => false,
        ]); ?>
