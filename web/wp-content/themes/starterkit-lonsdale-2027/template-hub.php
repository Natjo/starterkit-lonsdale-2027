<?php
/**
 * Template Name: Hub
 * Template Post Type: page
 */

$listing = new Theme_Listing([
    'post_type_meta' => 'hub_post_type',
    'search' => false,
    'taxonomies' => false,
    'sort' => false,
    'page_name' => 'hub_page',
]);

get_header();
common('header-nav');
?>

<main class="template-hub">
    <?php if ($listing->is_archive()) : ?>
        <?php hero('page', ['title' => $listing->archive_title()]); ?>
    <?php else : ?>
        <?php hero('page'); ?>
        <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
    <?php endif; ?>

    <section class="hub-results">
        <div class="hub-content">
            <?php if ($listing->has_results()) : ?>
                <?php component('list', [
                    'items' => $listing->items(),
                    'card' => $listing->card(),
                    'classes' => 'hub-list',
                ]); ?>
                <?php component('pagination', [
                    'total' => $listing->total_pages(),
                    'current' => $listing->current_page(),
                    'base' => $listing->pagination_base(),
                    'prev_text' => 'Précédent',
                    'next_text' => 'Suivant',
                    'aria_label' => 'Pagination des ' . $listing->post_type_label(),
                ]); ?>
            <?php else : ?>
                <p><?= esc_html(sprintf('Aucun contenu « %s » trouvé.', $listing->post_type_label())) ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
