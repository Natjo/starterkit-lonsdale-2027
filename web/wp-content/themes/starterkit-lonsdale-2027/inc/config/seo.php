<?php

/**
 * Données éditoriales du hero News utilisées comme secours par Yoast.
 *
 * @return array<string, mixed>
 */
function theme_get_news_seo_data(?int $post_id = null): array
{
    $post_id ??= get_queried_object_id();
    if ($post_id <= 0 || get_post_type($post_id) !== 'news' || !function_exists('get_field')) {
        return [];
    }

    static $cache = [];

    if (!array_key_exists($post_id, $cache)) {
        $hero = get_field('hero-news', $post_id);
        $cache[$post_id] = is_array($hero) && !array_is_list($hero) ? $hero : [];
    }

    return $cache[$post_id];
}

function theme_get_news_seo_image_id(?int $post_id = null): int
{
    $hero = theme_get_news_seo_data($post_id);
    $images = is_array($hero['images'] ?? null) ? $hero['images'] : [];

    foreach (['desktop', 'mobile'] as $key) {
        $image_id = is_numeric($images[$key] ?? null) ? (int) $images[$key] : 0;
        if ($image_id > 0) return $image_id;
    }

    return 0;
}

/**
 * Les entrées du CPT News sont des contenus éditoriaux, pas de simples pages.
 */
function theme_filter_yoast_article_type(mixed $type, mixed $indexable): mixed
{
    return is_object($indexable) && ($indexable->object_sub_type ?? '') === 'news'
        ? 'NewsArticle'
        : $type;
}

/**
 * Aligne les données Article sur le hero réellement visible.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function theme_filter_yoast_news_article(array $data): array
{
    if (!is_singular('news')) return $data;

    // Le contenu éditorial vit dans ACF, pas dans post_content : le calcul
    // automatique de Yoast ne représente donc pas un nombre de mots fiable.
    unset($data['wordCount']);

    $hero = theme_get_news_seo_data();
    $headline = $hero['title'] ?? '';
    $headline = is_scalar($headline) || $headline instanceof Stringable
        ? trim(wp_strip_all_tags((string) $headline))
        : '';

    if ($headline !== '') {
        $data['headline'] = $headline;
    }

    $date = $hero['date'] ?? '';
    $date = is_scalar($date) || $date instanceof Stringable ? trim((string) $date) : '';
    $published = DateTimeImmutable::createFromFormat('!d/m/Y', $date, wp_timezone());

    if ($published instanceof DateTimeImmutable && $published->format('d/m/Y') === $date) {
        $data['datePublished'] = $published->format('Y-m-d');
    }

    $image_id = theme_get_news_seo_image_id();
    $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'full') : false;

    if (is_string($image_url) && $image_url !== '') {
        $data['image'] = $image_url;
    }

    return $data;
}

/**
 * Utilise l'image du hero pour Open Graph lorsqu'aucune image Yoast spécifique
 * n'a été saisie pour la News.
 */
function theme_add_yoast_news_opengraph_image(mixed $images): mixed
{
    if (!is_singular('news') || !is_object($images) || !method_exists($images, 'add_image_by_id')) {
        return $images;
    }

    $post_id = get_queried_object_id();
    $manual_url = get_post_meta($post_id, '_yoast_wpseo_opengraph-image', true);
    $manual_id = (int) get_post_meta($post_id, '_yoast_wpseo_opengraph-image-id', true);

    if ($manual_url === '' && $manual_id <= 0) {
        $image_id = theme_get_news_seo_image_id($post_id);
        if ($image_id > 0) {
            $images->add_image_by_id($image_id);
        }
    }

    return $images;
}

/**
 * Même secours pour la Twitter Card, sans écraser une image définie par Yoast.
 */
function theme_filter_yoast_news_twitter_image(mixed $image): string
{
    $image = is_scalar($image) || $image instanceof Stringable ? trim((string) $image) : '';
    if ($image !== '' || !is_singular('news')) return $image;

    $image_id = theme_get_news_seo_image_id();
    $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'full') : false;

    return is_string($image_url) ? $image_url : '';
}

add_filter('wpseo_schema_article_type', 'theme_filter_yoast_article_type', 10, 2);
add_filter('wpseo_schema_article', 'theme_filter_yoast_news_article');
add_filter('wpseo_add_opengraph_images', 'theme_add_yoast_news_opengraph_image');
add_filter('wpseo_twitter_image', 'theme_filter_yoast_news_twitter_image');

// La pagination visible fournit déjà des liens crawlables. Évite que certains
// navigateurs préchargent automatiquement la page suivante depuis le <head>.
add_filter('wpseo_disable_adjacent_rel_links', '__return_true');
