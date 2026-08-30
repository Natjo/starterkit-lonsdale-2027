<?php
get_header();
common('header-nav');

$groups = [];

while (have_posts()) {
    the_post();

    $post_id = get_the_ID();
    $post_type = get_post_type($post_id);
    if (!is_string($post_type) || $post_type === '') continue;

    $post_type_object = get_post_type_object($post_type);
    $fallback_label = ucwords(str_replace(['-', '_'], ' ', $post_type));
    $section_label = trim((string) ($post_type_object?->labels->name ?? ''));
    $section_label = $section_label !== '' ? $section_label : $fallback_label;
    $type_label = trim((string) ($post_type_object?->labels->singular_name ?? ''));
    $type_label = $type_label !== '' ? $type_label : $section_label;

    $resolved_card = resolve_card_name($post_type);
    $card = $resolved_card !== '' ? $resolved_card : 'card-page';
    $title = trim((string) get_the_title($post_id));
    $item = [
        'post_id' => $post_id,
        'type' => $type_label,
        'title' => $title,
        'link' => [
            'url' => get_permalink($post_id),
            'title' => $title,
        ],
    ];

    $filtered_item = apply_filters(
        'theme_search_result_item',
        $item,
        $post_id,
        $post_type
    );
    if (is_array($filtered_item) && !array_is_list($filtered_item)) {
        $item = $filtered_item;
    }

    if (!isset($groups[$post_type])) {
        $groups[$post_type] = [
            'id' => 'search-section-' . sanitize_html_class($post_type),
            'label' => $section_label,
            'card' => $card,
            'items' => [],
        ];
    }

    $groups[$post_type]['items'][] = $item;
}

uasort($groups, static function (array $left, array $right): int {
    return strcasecmp((string) $left['label'], (string) $right['label']);
});

$search_query = get_search_query();
$found_posts = max(0, (int) ($GLOBALS['wp_query']->found_posts ?? 0));
$summary = $found_posts === 1 ? '1 résultat' : $found_posts . ' résultats';
?>

<main class="search-page">
    <header class="search-page-header">
        <?php component('title', [
            'title' => 'Résultats pour « ' . esc_html($search_query) . ' »',
            'hx' => 1,
            'classes' => 'title-1',
        ]); ?>

        <?php component('text', [
            'text' => '<p>' . esc_html($summary) . '</p>',
            'classes' => 'search-summary',
        ]); ?>

        <?php component('search', [
            'value' => $search_query,
            'action' => home_url('/'),
            'label' => 'Rechercher sur le site',
            'placeholder' => 'Votre recherche',
            'submit_label' => 'Rechercher',
            'classes' => 'search-page-form',
        ]); ?>
    </header>

    <?php if ($groups !== []) : ?>
        <div class="search-layout">
            <aside class="search-sidebar">
                <nav aria-label="Types de résultats">
                    <ul class="search-sidebar-list">
                        <?php foreach ($groups as $group) : ?>
                            <li>
                                <a href="#<?= esc_attr($group['id']) ?>">
                                    <?= esc_html($group['label']) ?>
                                    <span class="search-sidebar-count"><?= esc_html((string) count($group['items'])) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </aside>

            <div class="search-results">
                <?php foreach ($groups as $group) : ?>
                    <section
                        class="search-section"
                        id="<?= esc_attr($group['id']) ?>"
                        aria-labelledby="<?= esc_attr($group['id']) ?>-title"
                    >
                        <?php component('title', [
                            'title' => $group['label'],
                            'hx' => 2,
                            'classes' => 'title-2 search-section-title',
                            'attributes' => ['id' => $group['id'] . '-title'],
                        ]); ?>

                        <?php component('list', [
                            'items' => $group['items'],
                            'card' => $group['card'],
                            'classes' => 'search-list',
                        ]); ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>

        <?php component('pagination', [
            'total' => (int) ($GLOBALS['wp_query']->max_num_pages ?? 0),
            'current' => max(1, (int) get_query_var('paged')),
            'prev_text' => 'Précédent',
            'next_text' => 'Suivant',
            'aria_label' => 'Pagination des résultats',
        ]); ?>
    <?php else : ?>
        <?php component('text', [
            'text' => '<p>Aucun résultat trouvé.</p>',
            'classes' => 'search-empty',
        ]); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
