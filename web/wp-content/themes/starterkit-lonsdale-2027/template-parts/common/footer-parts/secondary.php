        <?php if (has_nav_menu('menu-footer-secondary')) : ?>
        <nav class="footer-secondary" aria-label="Liens complémentaires du pied de page">
            <?php wp_nav_menu(['theme_location' => 'menu-footer-secondary', 'container' => false, 'depth' => 1, 'menu_class' => 'menu-footer-secondary', 'items_wrap' => '<ul class="%2$s">%3$s</ul>', 'walker' => new Theme_Header_Nav_Walker(), 'fallback_cb' => false]); ?>
        </nav>
        <?php endif; ?>
