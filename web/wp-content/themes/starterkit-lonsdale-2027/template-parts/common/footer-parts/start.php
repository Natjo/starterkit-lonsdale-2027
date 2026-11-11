<?php
$footer_name = get_bloginfo('name');
?>
<footer id="footer" class="footer-type-<?= esc_attr($footer_type) ?>" role="contentinfo">
    <div class="footer-main">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= esc_url(home_url('/')) ?>" aria-label="<?= esc_attr($footer_name) ?>">
                <?php component('icon', ['name' => 'logo', 'width' => 54, 'height' => 55]); ?>
                <span><?= esc_html($footer_name) ?></span>
            </a>
