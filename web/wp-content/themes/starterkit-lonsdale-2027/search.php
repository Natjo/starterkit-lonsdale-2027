<?php
get_header();
common('header-nav');

$listing = new Theme_Listing([
    'query' => $GLOBALS['wp_query'] ?? null,
    'group_by' => 'post_type',
    'result_filter' => 'theme_search_result_item',
    'search' => [
        'name' => 's',
        'label' => 'Rechercher sur le site',
        'placeholder' => 'Votre recherche',
    ],
    'taxonomies' => false,
    'sort' => false,
    'ajax' => true,
    'ajax_group' => 'search-filters',
    'results_target' => 'search-results-region',
]);
$groups = $listing->grouped_items();

$search_query = get_search_query();
$found_posts = $listing->result_count();
$summary = $found_posts === 1 ? '1 résultat' : $found_posts . ' résultats';
?>

<main class="search-page">
    <div id="<?= esc_attr($listing->target()) ?>" class="search-results-region" aria-live="polite">
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

            <?php $listing->search(['classes' => 'search-page-form']); ?>
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
                'total' => $listing->total_pages(),
                'current' => $listing->current_page(),
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
    </div>
</main>

<?php get_footer(); ?>
