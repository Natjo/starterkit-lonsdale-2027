<?php
$page_404 = function_exists('get_field') ? get_field('page_404', 'option') : [];
$page_404 = is_array($page_404) && !array_is_list($page_404)
    ? $page_404
    : [
        'title' => 'Page introuvable',
        'text' => '<p>La page demandée n’existe pas ou a été déplacée.</p>',
        'link' => [
            'url' => home_url('/'),
            'title' => 'Retour à l’accueil',
            'target' => '',
        ],
    ];

get_header();
common('header-nav');
?>

<main class="page-404">
    <div class="page-404-content">
        <p class="page-404-code" aria-hidden="true">404</p>

        <?php component('title', [
            'title' => $page_404['title'] ?? '',
            'hx' => 1,
            'classes' => 'title-1 page-404-title',
        ]); ?>

        <?php component('text', [
            'text' => $page_404['text'] ?? '',
            'classes' => 'rte page-404-text',
        ]); ?>

        <?php component('btn', [
            'link' => is_array($page_404['link'] ?? null) ? $page_404['link'] : [],
            'classes' => 'btn-solid page-404-link',
        ]); ?>
    </div>
</main>

<?php get_footer();
