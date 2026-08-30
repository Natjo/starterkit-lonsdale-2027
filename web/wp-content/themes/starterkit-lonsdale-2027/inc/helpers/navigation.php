<?php

function getLanguages()
{
    if (!function_exists('icl_get_languages')) {
        return [];
    }

    $all_languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
    if (!is_array($all_languages)) {
        return [];
    }

    $result = [];

    foreach ($all_languages as $code => $language) {
        $result[] = [
            'code' => ucfirst($code),
            'url' => $language['url'],
            'active' => $code === (defined('ICL_LANGUAGE_CODE') ? constant('ICL_LANGUAGE_CODE') : 'fr'),
        ];
    }

    return $result;
}

function getMenu($menu_slug)
{
    $locations = get_nav_menu_locations();

    if (!empty($locations[$menu_slug])) {
        $menu = get_term($locations[$menu_slug]);
        $items = wp_get_nav_menu_items($menu->term_id);

        _wp_menu_item_classes_by_context($items);

        return $items;
    }

    return [];
}
