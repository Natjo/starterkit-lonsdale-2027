<?php

/**
 * Template Name: Filters
 * Template Post Type: page
 */

$filters = new Theme_Filters([
    'post_type' => 'project',
    'search' => [
        'label' => 'Rechercher',
        'placeholder' => 'Rechercher un projet',
        'acf_fields' => ['hero-page_title'],
    ],
    'taxonomies' => true,
    'taxonomy_relation' => 'AND',
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'ajax_target' => 'project-results',
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
    'submit_label' => 'Filtrer',
    'reset_label' => 'Réinitialiser',
    'aria_label' => 'Filtrer les projets',
    'result_labels' => [
        'singular' => 'projet',
        'plural' => 'projets',
    ],
]);
get_header();
common("header-nav");

?>

<main class="template-filters">
    <?php if ($filters->archive_title() !== '') : ?>
        <?php hero('page', ['title' => $filters->archive_title()]); ?>
    <?php else : ?>
        <?php hero('page'); ?>
    <?php endif; ?>

    <div class="layout-sidebar">
        <div class="main-header filters-search-bar">
            <?php $filters->search(); ?>
        </div>

        <div class="main-sidebar" aria-label="Filtrer les résultats">
            <?php $filters->filters(); ?>
        </div>

        <div class="main-content">
            <div class="filters-toolbar">
                <?php $filters->sort(); ?>
            </div>

            <div id="<?= esc_attr($filters->target()) ?>" class="filters-results-region" aria-live="polite">
                <?php $filters->results_message(); ?>
                <?php if ($filters->has_results()) : ?>
                    <?php component('list', [
                        'items' => $filters->items(),
                        'card' => $filters->card(),
                        'classes' => 'filters-results',
                    ]); ?>

                    <?php component('pagination', [
                        'total' => $filters->total_pages(),
                        'current' => $filters->current_page(),
                        'prev_text' => 'Précédent',
                        'next_text' => 'Suivant',
                        'aria_label' => 'Pagination des ' . $filters->post_type_label(),
                    ]); ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<?php get_footer();
