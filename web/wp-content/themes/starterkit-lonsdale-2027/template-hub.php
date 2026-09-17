<?php
/**
 * Template Name: Hub
 * Template Post Type: page
 */

$is_post_type_archive = is_post_type_archive();
$queried_object = get_queried_object();
$archive_post_type = $queried_object instanceof WP_Post_Type ? $queried_object->name : '';
$configured_post_type = !$is_post_type_archive
    ? get_post_meta(get_queried_object_id(), 'hub_post_type', true)
    : '';
$configured_post_type = is_string($configured_post_type) ? sanitize_key($configured_post_type) : '';
$hub_post_type = $is_post_type_archive ? $archive_post_type : $configured_post_type;
$post_type_object = $hub_post_type !== '' ? get_post_type_object($hub_post_type) : null;
$hub_post_type = $post_type_object instanceof WP_Post_Type ? $hub_post_type : '';
$post_type_label = trim((string) ($post_type_object?->labels->name ?? ''));
$post_type_label = $post_type_label !== '' ? $post_type_label : 'contenus';
$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$hub_query = $is_post_type_archive
    ? $GLOBALS['wp_query']
    : ($hub_post_type !== '' ? new WP_Query([
        'post_type' => $hub_post_type,
        'post_status' => 'publish',
        'paged' => $paged,
    ]) : null);
$configured_card = $hub_post_type !== ''
    ? apply_filters('theme_content_type_card', $hub_post_type, $hub_post_type)
    : '';
$card_name = resolve_card_name($configured_card);
$card_name = $card_name !== '' ? $card_name : 'page';
$items = [];

if ($hub_query instanceof WP_Query) {
    while ($hub_query->have_posts()) {
        $hub_query->the_post();
        $post_id = get_the_ID();
        $title = trim((string) get_the_title($post_id));
        $item = [
            'post_id' => $post_id,
            'type' => trim((string) ($post_type_object?->labels->singular_name ?? '')),
            'date' => get_the_date('', $post_id),
            'datetime' => get_the_date('Y-m-d', $post_id),
            'title' => $title,
            'text' => get_the_excerpt($post_id),
            'image' => get_post_thumbnail_id($post_id),
            'link' => ['url' => get_permalink($post_id), 'title' => $title],
        ];
        $filtered_item = apply_filters('theme_hub_result_item', $item, $post_id, $hub_post_type);
        $items[] = is_array($filtered_item) && !array_is_list($filtered_item) ? $filtered_item : $item;
    }
}

if (!$is_post_type_archive) wp_reset_postdata();

get_header();
common('header-nav');
?>

<main class="template-hub">
    <?php if ($is_post_type_archive) : ?>
        <?php hero('page', ['title' => post_type_archive_title('', false)]); ?>
    <?php else : ?>
        <?php hero('page'); ?>
        <?php get_template_part('template-parts/utils/dispatch-strates'); ?>
    <?php endif; ?>

    <section class="hub-results">
        <div class="hub-content">
            <?php if ($items !== []) : ?>
                <?php component('list', ['items' => $items, 'card' => $card_name, 'classes' => 'hub-list']); ?>
                <?php component('pagination', [
                    'total' => $hub_query instanceof WP_Query ? (int) $hub_query->max_num_pages : 0,
                    'current' => $paged,
                    'prev_text' => 'Précédent',
                    'next_text' => 'Suivant',
                    'aria_label' => 'Pagination des ' . $post_type_label,
                ]); ?>
            <?php else : ?>
                <p><?= esc_html(sprintf('Aucun contenu « %s » trouvé.', $post_type_label)) ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
