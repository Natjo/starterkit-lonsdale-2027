<?php

add_action('acf/init', function () {
    if (!function_exists('acf_add_options_page')) return;

    acf_add_options_page([
        'page_title' => 'Options du thème',
        'menu_title' => 'Options',
        'menu_slug' => 'theme-options',
        'capability' => 'manage_options',
        'redirect' => false,
    ]);

    acf_add_options_sub_page([
        'page_title' => 'Prefooter',
        'menu_title' => 'Prefooter',
        'menu_slug' => 'theme-options-prefooter',
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

add_filter('acf/load_field/key=field_6a64efd427ede', function (array $field): array {
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
