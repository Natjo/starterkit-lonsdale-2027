<?php
/**
 * Exemple d’archive de CPT filtrable.
 */

$filters = new Theme_Listing([
    'post_type' => 'team',
    'search' => false,
    'taxonomies' => ['team_role'],
    'taxonomy_relation' => 'AND',
    'ajax' => true,
    'ajax_group' => 'team-filters',
    'results_target' => 'team-results',
    'card' => 'team',
    'sort' => [
        'label' => 'Sort by',
        'default' => 'date_desc',
        'options' => [
            'date_desc' => ['label' => 'Newest', 'orderby' => 'date', 'order' => 'DESC'],
            'date_asc' => ['label' => 'Oldest', 'orderby' => 'date', 'order' => 'ASC'],
            'title_asc' => ['label' => 'Name A–Z', 'orderby' => 'title', 'order' => 'ASC'],
            'title_desc' => ['label' => 'Name Z–A', 'orderby' => 'title', 'order' => 'DESC'],
        ],
    ],
    'submit_label' => 'Filter',
    'reset_label' => 'Reset',
    'aria_label' => 'Filter team members',
    'result_labels' => [
        'singular' => 'member',
        'plural' => 'members',
    ],
]);

get_header();
common('header-nav');
?>

<main class="archive-team">
    <?php hero('page', ['title' => $filters->archive_title()]); ?>

    <section class="team-archive" aria-label="Team">
        <div class="team-archive-controls">
            <?php $filters->filters(); ?>
            <?php $filters->sort(); ?>
        </div>

        <div id="<?= esc_attr($filters->target()) ?>" class="team-results filters-results-region" aria-live="polite">
            <?php $filters->results_message([
                'messages' => [
                    'zero' => 'No member matches your filters.',
                    'singular' => '{count} member matches your filters.',
                    'plural' => '{count} members match your filters.',
                ],
            ]); ?>

            <?php if ($filters->has_results()) : ?>
                <?php component('list', [
                    'items' => $filters->items(),
                    'card' => $filters->card(),
                    'classes' => 'team-archive-list',
                ]); ?>

                <?php component('pagination', [
                    'total' => $filters->total_pages(),
                    'current' => $filters->current_page(),
                    'prev_text' => 'Previous',
                    'next_text' => 'Next',
                    'aria_label' => 'Team pagination',
                ]); ?>
            <?php else : ?>
                <p class="team-empty">No member found.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
