        </div>
        <?php if (has_nav_menu('menu-footer')) : ?>
        <nav class="footer-navigation" aria-label="Navigation de pied de page">
            <?php wp_nav_menu(['theme_location' => 'menu-footer', 'container' => false, 'depth' => 2, 'menu_class' => 'footer-columns', 'items_wrap' => '<ul class="%2$s">%3$s</ul>', 'walker' => new Theme_Footer_Columns_Walker(), 'fallback_cb' => false]); ?>
        </nav>
        <?php endif; ?>
