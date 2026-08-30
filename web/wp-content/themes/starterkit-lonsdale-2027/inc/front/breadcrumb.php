<?php

/**
 * Normalise les éléments partagés par le composant et le JSON-LD.
 *
 * @return list<array{name: string, url: string}>
 */
function theme_normalize_breadcrumb_items(mixed $items): array
{
    if (!is_array($items)) return [];

    $normalized = [];

    foreach ($items as $item) {
        if (!is_array($item) || array_is_list($item)) continue;

        $name = $item['name'] ?? '';
        $name = is_scalar($name) || $name instanceof Stringable
            ? trim(wp_strip_all_tags((string) $name))
            : '';

        if ($name === '') continue;

        $url = $item['url'] ?? '';
        $url = is_scalar($url) || $url instanceof Stringable
            ? esc_url_raw(trim((string) $url))
            : '';

        $normalized[] = [
            'name' => $name,
            'url' => $url,
        ];
    }

    return $normalized;
}

/**
 * Construit le parcours de la requête WordPress courante.
 *
 * @return list<array{name: string, url: string}>
 */
function theme_get_breadcrumb_items(): array
{
    $labels = apply_filters('theme_breadcrumb_labels', [
        'home' => 'Accueil',
        'posts' => 'Actualités',
        'search' => 'Résultats pour « %s »',
        '404' => 'Page introuvable',
    ]);
    $labels = is_array($labels) ? $labels : [];

    $label = static function (string $key, string $fallback) use ($labels): string {
        $value = $labels[$key] ?? $fallback;
        return is_scalar($value) || $value instanceof Stringable
            ? trim((string) $value)
            : $fallback;
    };

    $items = [];
    $append = static function (string $name, mixed $url = '') use (&$items): void {
        $items[] = ['name' => $name, 'url' => $url];
    };

    if (!is_front_page()) {
        $append($label('home', 'Accueil'), home_url('/'));

        if (is_home()) {
            $posts_page_id = (int) get_option('page_for_posts');
            $append(
                $posts_page_id > 0 ? get_the_title($posts_page_id) : $label('posts', 'Actualités')
            );
        } elseif (is_singular()) {
            $post = get_queried_object();

            if ($post instanceof WP_Post) {
                if ($post->post_type === 'page') {
                    foreach (array_reverse(get_post_ancestors($post)) as $ancestor_id) {
                        $append(get_the_title($ancestor_id), get_permalink($ancestor_id));
                    }
                } elseif ($post->post_type === 'attachment' && $post->post_parent > 0) {
                    $append(
                        get_the_title($post->post_parent),
                        get_permalink($post->post_parent)
                    );
                } elseif ($post->post_type === 'post') {
                    $posts_page_id = (int) get_option('page_for_posts');
                    if ($posts_page_id > 0) {
                        $append(get_the_title($posts_page_id), get_permalink($posts_page_id));
                    }
                } else {
                    $post_type = get_post_type_object($post->post_type);
                    $archive_url = get_post_type_archive_link($post->post_type);

                    if ($post_type instanceof WP_Post_Type && is_string($archive_url) && $archive_url !== '') {
                        $append($post_type->labels->name, $archive_url);
                    }
                }

                $append(get_the_title($post));
            }
        } elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();

            if ($term instanceof WP_Term) {
                $taxonomy = get_taxonomy($term->taxonomy);
                $object_types = $taxonomy instanceof WP_Taxonomy ? $taxonomy->object_type : [];

                foreach ($object_types as $object_type) {
                    if ($object_type === 'post') {
                        $posts_page_id = (int) get_option('page_for_posts');
                        if ($posts_page_id > 0) {
                            $append(get_the_title($posts_page_id), get_permalink($posts_page_id));
                        }
                        break;
                    }

                    $post_type = get_post_type_object($object_type);
                    $archive_url = get_post_type_archive_link($object_type);

                    if ($post_type instanceof WP_Post_Type && is_string($archive_url) && $archive_url !== '') {
                        $append($post_type->labels->name, $archive_url);
                        break;
                    }
                }

                foreach (array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy')) as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, $term->taxonomy);
                    if (!$ancestor instanceof WP_Term) continue;

                    $ancestor_url = get_term_link($ancestor);
                    if (is_wp_error($ancestor_url)) continue;

                    $append($ancestor->name, $ancestor_url);
                }

                $append($term->name);
            }
        } elseif (is_post_type_archive()) {
            $post_type_name = get_query_var('post_type');
            $post_type_name = is_array($post_type_name) ? reset($post_type_name) : $post_type_name;
            $post_type = is_string($post_type_name) ? get_post_type_object($post_type_name) : null;

            if ($post_type instanceof WP_Post_Type) {
                $append($post_type->labels->name);
            }
        } elseif (is_search()) {
            $search_label = $label('search', 'Résultats pour « %s »');
            $append(sprintf($search_label, get_search_query()));
        } elseif (is_404()) {
            $append($label('404', 'Page introuvable'));
        } elseif (is_archive()) {
            $append(get_the_archive_title());
        }
    }

    /**
     * Personnalise le parcours final sans modifier le composant.
     *
     * @param list<array{name: string, url: string}> $items
     */
    $items = apply_filters('theme_breadcrumb_items', $items, get_queried_object());

    return theme_normalize_breadcrumb_items($items);
}

/**
 * @param list<array{name: string, url: string}> $items
 * @return list<array<string, int|string>>
 */
function theme_get_breadcrumb_schema_elements(array $items): array
{
    if (count($items) < 2) return [];

    $elements = [];
    $last_index = array_key_last($items);

    foreach ($items as $index => $item) {
        if (
            !isset($item['name'], $item['url'])
            || !is_string($item['name'])
            || !is_string($item['url'])
            || $item['name'] === ''
            || ($index !== $last_index && $item['url'] === '')
        ) {
            return [];
        }

        $element = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
        ];

        if ($index !== $last_index) {
            $element['item'] = $item['url'];
        }

        $elements[] = $element;
    }

    return $elements;
}

/**
 * Aligne le BreadcrumbList de Yoast sur le parcours visible du thème.
 *
 * @param array<string, mixed> $piece
 * @return array<string, mixed>
 */
function theme_filter_yoast_breadcrumb_schema(array $piece): array
{
    $elements = theme_get_breadcrumb_schema_elements(theme_get_breadcrumb_items());

    if ($elements !== []) {
        $piece['itemListElement'] = $elements;
    }

    return $piece;
}

/**
 * Produit le BreadcrumbList du thème lorsque le schéma n'est pas géré par Yoast.
 */
function theme_render_breadcrumb_schema(): void
{
    if (is_admin() || is_front_page() || is_404()) return;

    $yoast_manages_schema = defined('WPSEO_VERSION') || did_action('wpseo_head') > 0;
    $enabled = (bool) apply_filters(
        'theme_breadcrumb_schema_enabled',
        !$yoast_manages_schema
    );

    if (!$enabled) return;

    $items = theme_get_breadcrumb_items();
    $elements = theme_get_breadcrumb_schema_elements($items);
    if ($elements === []) return;

    $json = wp_json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    if (!is_string($json) || $json === '') return;

    echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
}

add_action('wp_head', 'theme_render_breadcrumb_schema', 30);
add_filter('wpseo_schema_breadcrumb', 'theme_filter_yoast_breadcrumb_schema', 11);
