<?php
require_once __DIR__ . '/../../inc/front/header-nav-walker.php';
$location = 'menu-footer';
$nav_label = 'Navigation de pied de page';
$copyright = sprintf('© %s %s', wp_date('Y'), get_bloginfo('name'));
?>

<footer id="footer" role="contentinfo">
    <?php if (has_nav_menu($location)) : ?>
        <nav aria-label="<?= esc_attr($nav_label) ?>">
            <?php
            wp_nav_menu([
                'theme_location' => $location,
                'container' => false,
                'menu_class' => 'footer-menu',
                'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                'walker' => new Theme_Header_Nav_Walker(),
                'fallback_cb' => false,
            ]);
            ?>
        </nav>
    <?php endif ?>

    <small><?= esc_html($copyright) ?></small>
</footer>
