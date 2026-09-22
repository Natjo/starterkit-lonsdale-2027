<?php
/** Export published WordPress pages for the Forge editorial page catalogue. */

if (!defined('WP_CLI') || !WP_CLI || !function_exists('get_fields')) {
    throw new RuntimeException('Cet export requiert WP-CLI et ACF actif.');
}

$posts = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    'suppress_filters' => true,
]);

$home_id = (int) get_option('page_on_front');
$pages_by_parent = [];
$included_ids = array_fill_keys(array_map(static fn (WP_Post $post): int => (int) $post->ID, $posts), true);
foreach ($posts as $post) {
    $parent_id = (int) $post->post_parent;
    $pages_by_parent[isset($included_ids[$parent_id]) ? $parent_id : 0][] = $post;
}

$build_pages = static function (int $parent_id) use (&$build_pages, $pages_by_parent, $home_id): array {
    $pages = [];
    foreach ($pages_by_parent[$parent_id] ?? [] as $post) {
        $id = (int) $post->ID;
        $fields = get_fields($id, false);
        $fields = is_array($fields) ? $fields : [];
        $hero_values = [];
        foreach ($fields as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'hero-') && is_array($value)) {
                $hero_values[substr($key, 5)] = $value;
            }
        }

        $strates = [];
        foreach (is_array($fields['strates'] ?? null) ? $fields['strates'] : [] as $index => $row) {
            if (!is_array($row) || !is_string($row['acf_fc_layout'] ?? null)) {
                continue;
            }
            $slug = $row['acf_fc_layout'];
            unset($row['acf_fc_layout']);
            $strates[] = [
                'id' => 'starter-' . $id . '-' . ($index + 1),
                'slug' => $slug,
                'values' => $row,
            ];
        }

        $template = get_page_template_slug($id);
        $pages[] = [
            'name' => wp_strip_all_tags(get_the_title($id)),
            'slug' => $post->post_name,
            'type' => 'page',
            'contentMode' => 'content-type',
            'template' => is_string($template) && $template !== '' ? $template : 'default',
            'isHome' => $id === $home_id,
            'heroValues' => (object) $hero_values,
            'strates' => $strates,
            'children' => $build_pages($id),
        ];
    }
    return $pages;
};

$json = wp_json_encode(['schemaVersion' => 1, 'pages' => $build_pages(0)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    throw new RuntimeException('Impossible de sérialiser le catalogue des pages.');
}
WP_CLI::line($json);
