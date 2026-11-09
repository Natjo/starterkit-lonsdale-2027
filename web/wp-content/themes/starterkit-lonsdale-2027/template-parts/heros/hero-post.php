<?php
$args = get_template_args();
$post_id = get_the_ID();
$tags = [];

if ($post_id) {
    $taxonomies = get_object_taxonomies(get_post_type($post_id), 'objects');
    foreach ($taxonomies as $taxonomy) {
        if (!$taxonomy->public) continue;
        $terms = get_the_terms($post_id, $taxonomy->name);
        if (!is_array($terms)) continue;
        foreach ($terms as $term) {
            $name = trim((string) $term->name);
            if ($name !== '') $tags[] = $name;
        }
    }
}
?>

<header class="hero hero-post">
    <div class="hero-content">
        <?php if ($tags !== []) : ?>
            <div class="post-tags">
                <?php foreach ($tags as $tag) : ?>
                    <?php component('tag', ['name' => $tag]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php component('title', [
            'title' => $args['title'] ?? '',
            'hx' => 1,
            'classes' => 'title-1 hero-title',
        ]); ?>

        <?php component('date', [
            'date' => $args['date'] ?? '',
            'classes' => 'post-date',
        ]); ?>
    </div>
    
    <?php component('picture', [
        'images' => $args['images'] ?? [],
        'classes' => 'hero-image',
        'fit' => "cover",
        'preload' => true,
    ]); ?>
</header>