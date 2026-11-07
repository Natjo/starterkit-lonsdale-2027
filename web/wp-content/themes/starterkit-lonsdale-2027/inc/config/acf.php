<?php

/**
 * Conserve le nom lisible d'un fichier Local JSON déjà versionné.
 *
 * Sans ce filtre, ACF enregistre une nouvelle copie nommée d'après la clé
 * technique du groupe et laisse l'ancien fichier à côté.
 */
add_filter('acf/json/save_file_name', static function (string $filename, array $post, string $load_path): string {
    if ($load_path === '') {
        return $filename;
    }

    $existing_filename = basename($load_path);

    return pathinfo($existing_filename, PATHINFO_EXTENSION) === 'json'
        ? $existing_filename
        : $filename;
}, 10, 3);

/**
 * Conserve les sous-champs des anciennes lignes du flexible content après
 * renommage des layouts du catalogue.
 *
 * ACF stocke les noms des layouts dans la valeur brute du champ `strates`.
 * Ce filtre doit intervenir avant son propre `load_value` (priorité 10).
 */
function theme_normalize_strate_layout_names(mixed $value, mixed $post_id, array $field): mixed
{
    if (($field['key'] ?? '') !== 'field_669e66a6b1d81' || !is_array($value)) return $value;

    foreach ($value as &$layout) {
        if ($layout === 'text_image') $layout = 'text-image';
        if ($layout === 'text_animated') $layout = 'text-animated';
        if ($layout === 'content-columns') $layout = 'columns-content';
    }
    unset($layout);

    return $value;
}
add_filter('acf/load_value', 'theme_normalize_strate_layout_names', 5, 3);

add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) return;

    acf_add_options_page([
        'page_title' => 'Options du thème',
        'menu_title' => 'Options',
        'menu_slug' => 'theme-options',
        'capability' => 'manage_options',
        'redirect' => true,
    ]);

    acf_add_options_sub_page([
        'page_title' => 'Header',
        'menu_title' => 'Header',
        'menu_slug' => 'theme-options-header',
        'parent_slug' => 'theme-options',
        'capability' => 'manage_options',
    ]);

    acf_add_options_sub_page([
        'page_title' => 'Prefooter',
        'menu_title' => 'Prefooter',
        'menu_slug' => 'theme-options-prefooter',
        'parent_slug' => 'theme-options',
        'capability' => 'manage_options',
    ]);

    acf_add_options_sub_page([
        'page_title' => 'Popup',
        'menu_title' => 'Popup',
        'menu_slug' => 'theme-options-popup',
        'parent_slug' => 'theme-options',
        'capability' => 'manage_options',
    ]);

    acf_add_options_sub_page([
        'page_title' => 'Page 404',
        'menu_title' => 'Page 404',
        'menu_slug' => 'theme-options-404',
        'parent_slug' => 'theme-options',
        'capability' => 'manage_options',
    ]);

    add_action('admin_menu', static function (): void {
        remove_submenu_page('theme-options', 'theme-options');
    }, 999);
});

/**
 * Retourne les CPT possédant une card de même nom.
 *
 * @return array<string, string>
 */
function theme_card_post_type_choices(): array
{
    if (!function_exists('get_post_types') || !function_exists('resolve_card_name')) {
        return [];
    }

    $choices = [];
    $post_types = get_post_types(['show_ui' => true], 'objects');

    foreach ($post_types as $post_type) {
        if (
            !is_object($post_type)
            || empty($post_type->name)
            || resolve_card_name((string) $post_type->name) === ''
        ) {
            continue;
        }

        $label = $post_type->labels->singular_name ?? $post_type->label ?? $post_type->name;
        $choices[(string) $post_type->name] = (string) $label;
    }

    return $choices;
}

add_filter('acf/load_field/key=field_6a64efd427ede_auto', function (array $field): array {
    $field['choices'] = theme_card_post_type_choices();

    if (!isset($field['choices'][$field['default_value'] ?? ''])) {
        $field['default_value'] = array_key_first($field['choices']) ?? '';
    }

    return $field;
});

add_filter('acf/load_field/key=field_6a64efd427ede_more', function (array $field): array {
    $field['choices'] = theme_card_post_type_choices();
    if (!isset($field['choices'][$field['default_value'] ?? ''])) {
        $field['default_value'] = array_key_first($field['choices']) ?? '';
    }
    return $field;
});

add_filter('acf/load_field/key=field_6a64efd427ede_slider', function (array $field): array {
    $field['choices'] = theme_card_post_type_choices();
    if (!isset($field['choices'][$field['default_value'] ?? ''])) {
        $field['default_value'] = array_key_first($field['choices']) ?? '';
    }
    return $field;
});

add_filter('acf/load_field/key=field_6a64efd427ee6', function (array $field): array {
    $field['post_type'] = array_keys(theme_card_post_type_choices());
    $field['filters'] = count($field['post_type']) > 1
        ? ['search', 'post_type']
        : ['search'];

    return $field;
});

add_filter('acf/load_field/key=field_6a66a2a10a106', function (array $field): array {
    $field['choices'] = theme_card_post_type_choices();

    if (!isset($field['choices'][$field['default_value'] ?? ''])) {
        $field['default_value'] = array_key_first($field['choices']) ?? '';
    }

    return $field;
});

add_filter('acf/load_field/key=field_6a66a2a10a107', function (array $field): array {
    $field['post_type'] = array_keys(theme_card_post_type_choices());
    $field['filters'] = count($field['post_type']) > 1
        ? ['search', 'post_type']
        : ['search'];

    return $field;
});

add_filter('acf/load_field/key=field_6a66b2b10b106', function (array $field): array {
    $field['choices'] = theme_card_post_type_choices();

    if (!isset($field['choices'][$field['default_value'] ?? ''])) {
        $field['default_value'] = array_key_first($field['choices']) ?? '';
    }

    return $field;
});

add_filter('acf/load_field/key=field_6a66b2b10b107', function (array $field): array {
    $field['post_type'] = array_keys(theme_card_post_type_choices());
    $field['filters'] = count($field['post_type']) > 1
        ? ['search', 'post_type']
        : ['search'];

    return $field;
});

/** Preserve existing hero data when the ACF group is renamed from news to post. */
function theme_load_legacy_post_hero_value(mixed $value, mixed $post_id, array $field): mixed
{
    $name = $field['name'] ?? '';
    if (!is_string($name)
        || ($name !== 'hero-post' && !str_starts_with($name, 'hero-post_'))
        || !is_numeric($post_id) || (int) $post_id <= 0
        || metadata_exists('post', (int) $post_id, $name)) {
        return $value;
    }

    $legacy_name = 'hero-news' . substr($name, strlen('hero-post'));
    return metadata_exists('post', (int) $post_id, $legacy_name)
        ? get_post_meta((int) $post_id, $legacy_name, true)
        : $value;
}
add_filter('acf/load_value', 'theme_load_legacy_post_hero_value', 6, 3);
