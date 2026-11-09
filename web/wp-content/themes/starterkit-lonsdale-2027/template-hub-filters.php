<?php

/**
 * Template Name: Hub filters
 * Template Post Type: page
 */

$listing = new Theme_Listing([
    'post_type_meta' => 'hub_post_type',
    'search' => [
        'label' => 'Rechercher',
        'placeholder' => 'Rechercher',
    ],
    'taxonomies' => true,
    'taxonomy_relation' => 'AND',
    'sort' => [
        'label' => 'Trier par',
        'default' => 'date_desc',
        'options' => [
            'date_desc' => ['label' => 'Plus récents', 'orderby' => 'date', 'order' => 'DESC'],
            'date_asc' => ['label' => 'Plus anciens', 'orderby' => 'date', 'order' => 'ASC'],
            'title_asc' => ['label' => 'Titre A–Z', 'orderby' => 'title', 'order' => 'ASC'],
            'title_desc' => ['label' => 'Titre Z–A', 'orderby' => 'title', 'order' => 'DESC'],
        ],
    ],
    'pagination_slug' => 'hub_page',
    'ajax' => true,
    'ajax_group' => 'hub-filters',
    'results_target' => 'hub-filter-results',
    'pretty_urls' => true,
    'submit_label' => 'Filtrer',
    'reset_label' => 'Réinitialiser',
    'aria_label' => 'Filtrer les contenus',
]);

get_header();
common('header-nav');
?>

<main class="template-hub-filters">
    <?php if ($listing->is_archive()) : ?>
        <?php hero('page', ['title' => $listing->archive_title()]); ?>
    <?php else : ?>
        <?php hero('page'); ?>
        <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
    <?php endif; ?>

    <div class="main-content">
        <div class="filters">
            <?php $listing->search(); ?>
            <?php $listing->filters(); ?>
            <?php $listing->sort(); ?>
        </div>

        <div class="results" id="<?= esc_attr($listing->target()) ?>" aria-live="polite">
            <?php $listing->results_message(); ?>
            <?php if ($listing->has_results()) : ?>
                <?php component('list', [
                    'items' => $listing->items(),
                    'card' => $listing->card(),
                    'classes' => 'results-list',
                ]); ?>

                <?php component('pagination', [
                    'total' => $listing->total_pages(),
                    'current' => $listing->current_page(),
                    'base' => $listing->pagination_base(),
                    'prev_text' => 'Précédent',
                    'next_text' => 'Suivant',
                    'aria_label' => 'Pagination des ' . $listing->post_type_label(),
                ]); ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>