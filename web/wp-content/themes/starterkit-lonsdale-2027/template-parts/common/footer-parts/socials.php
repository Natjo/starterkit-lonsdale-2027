<?php
$footer_locations = get_nav_menu_locations();
$footer_social_entries = !empty($footer_locations['menu-footer-socials']) ? wp_get_nav_menu_items($footer_locations['menu-footer-socials']) : [];
$footer_socials = [];
foreach (is_array($footer_social_entries) ? $footer_social_entries : [] as $entry) {
    if ((int) $entry->menu_item_parent !== 0) continue;
    foreach (is_array($entry->classes ?? null) ? $entry->classes : [] as $network) {
        $network = preg_replace('/^icon-/', '', (string) $network);
        if (!in_array($network, ['facebook','instagram','linkedin','x','youtube','tiktok','pinterest','threads'], true)) continue;
        $footer_socials[] = ['network' => $network, 'url' => $entry->url];
        break;
    }
}
component('socials', ['items' => $footer_socials, 'classes' => 'footer-socials', 'aria_label' => 'Réseaux sociaux']);
?>
