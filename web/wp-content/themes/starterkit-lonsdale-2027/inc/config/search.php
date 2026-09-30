<?php

add_action('pre_get_posts', static function (WP_Query $query): void {
    if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return;
    }

    $post_types = get_post_types([
        'public' => true,
        'exclude_from_search' => false,
    ], 'names');

    $excluded_post_types = ['attachment'];

    if (function_exists('theme_hidden_post_types')) {
        $excluded_post_types = [
            ...$excluded_post_types,
            ...theme_hidden_post_types(),
        ];
    }

    $query->set(
        'post_type',
        array_values(array_diff(
            array_unique(array_map('strval', $post_types)),
            array_unique($excluded_post_types)
        ))
    );
});
