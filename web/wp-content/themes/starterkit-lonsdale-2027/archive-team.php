<?php
/**
 * Exemple d’archive de CPT autonome.
 */

$items = [];

while (have_posts()) {
    the_post();
    $post_id = get_the_ID();
    $title = trim((string) get_the_title($post_id));
    if ($title === '') continue;

    $items[] = [
        'type' => 'Member',
        'title' => $title,
        'text' => get_the_excerpt($post_id),
        'link' => [
            'url' => get_permalink($post_id),
            'title' => $title,
        ],
    ];
}

$paged = max(1, (int) get_query_var('paged'));
$total_pages = isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query
    ? (int) $GLOBALS['wp_query']->max_num_pages
    : 0;

get_header();
common('header-nav');
?>

<main>
    <?php hero('page', ['title' => post_type_archive_title('', false)]); ?>

    <section class="team-archive" aria-label="Team">
        <?php if ($items !== []) : ?>
            <?php component('list', [
                'items' => $items,
                'card' => 'page',
                'classes' => 'team-archive-list',
            ]); ?>

            <?php component('pagination', [
                'total' => $total_pages,
                'current' => $paged,
                'prev_text' => 'Previous',
                'next_text' => 'Next',
                'aria_label' => 'Team pagination',
            ]); ?>
        <?php else : ?>
            <p>No member found.</p>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>
