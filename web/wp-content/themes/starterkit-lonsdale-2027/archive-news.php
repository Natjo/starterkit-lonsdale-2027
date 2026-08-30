<?php
get_header();
common('header-nav');

$items = [];

while (have_posts()) {
    the_post();

    $post_id = get_the_ID();
    $hero = function_exists('get_field') ? get_field('hero-news', $post_id) : [];
    $hero = is_array($hero) && !array_is_list($hero) ? $hero : [];
    $images = is_array($hero['images'] ?? null) && !array_is_list($hero['images'])
        ? $hero['images']
        : [];

    $title = is_scalar($hero['title'] ?? null) || ($hero['title'] ?? null) instanceof Stringable
        ? trim((string) $hero['title'])
        : '';
    if ($title === '') {
        $title = trim((string) get_the_title($post_id));
    }

    $items[] = [
        'post_id' => $post_id,
        'date' => $hero['date'] ?? '',
        'title' => $title,
        'image' => $images['desktop'] ?? null,
        'link' => [
            'url' => get_permalink($post_id),
            'title' => $title,
        ],
    ];
}
?>

<main>
    <?php hero('page', [
        'title' => post_type_archive_title('', false),
    ]); ?>

    <section class="archive-news-results">
        <div class="archive-news-content">
            <?php if ($items !== []) : ?>
                <?php component('list', [
                    'items' => $items,
                    'card' => 'news',
                    'classes' => 'news-list',
                ]); ?>

                <?php component('pagination', [
                    'total' => (int) ($GLOBALS['wp_query']->max_num_pages ?? 0),
                    'current' => max(1, (int) get_query_var('paged')),
                    'prev_text' => 'Précédent',
                    'next_text' => 'Suivant',
                    'aria_label' => 'Pagination des actualités',
                ]); ?>
            <?php else : ?>
                <p><?= esc_html__('Aucune actualité trouvée.', 'theme') ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
