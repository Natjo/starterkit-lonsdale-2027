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

/**
 * Enrichit les résultats propres au catalogue du starter.
 *
 * Lorsqu'un projet renomme ou retire un CPT, adapter uniquement ce filtre :
 * le template search.php reste indépendant des types de contenus.
 *
 * @param array<string, mixed> $item
 * @return array<string, mixed>
 */
add_filter('theme_search_result_item', static function (
    array $item,
    int $post_id,
    string $post_type
): array {
    if (!function_exists('get_field')) return $item;

    if ($post_type === 'news') {
        $hero = get_field('hero-news', $post_id);
        $hero = is_array($hero) && !array_is_list($hero) ? $hero : [];
        $images = is_array($hero['images'] ?? null) && !array_is_list($hero['images'])
            ? $hero['images']
            : [];
        $hero_title = is_scalar($hero['title'] ?? null) || ($hero['title'] ?? null) instanceof Stringable
            ? trim((string) $hero['title'])
            : '';

        $item['date'] = $hero['date'] ?? '';
        $item['title'] = $hero_title !== '' ? $hero_title : $item['title'];
        $item['image'] = $images['desktop'] ?? null;
    } elseif ($post_type === 'page') {
        $hero = get_field('hero-page', $post_id);
        $hero = is_array($hero) && !array_is_list($hero) ? $hero : [];
        $item['text'] = $hero['text'] ?? '';
    }

    return $item;
}, 10, 3);
