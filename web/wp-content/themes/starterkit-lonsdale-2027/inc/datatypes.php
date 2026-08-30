<?php

add_action('init', function () {
    register_post_type('news', [
        'label'               => __('News'),
        'labels'              => [
            'name'               => __('News'),
            'singular_name'      => __('News'),
            'add_new'            => __('Ajouter'),
            'add_new_item'       => __('Ajouter une News'),
            'edit_item'          => __('Modifier la News'),
            'new_item'           => __('Nouvelle News'),
            'view_item'          => __('Voir la News'),
            'search_items'       => __('Rechercher une News'),
            'not_found'          => __('Aucune News trouvée'),
            'not_found_in_trash' => __('Aucune News dans la corbeille'),
            'all_items'          => __('Toutes les News'),
            'menu_name'          => __('News'),
        ],
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'has_archive'         => 'news',
        'rewrite'             => [
            'slug'       => 'news',
            'with_front' => false,
        ],
        'query_var'           => true,
        'supports'            => ['title', 'thumbnail', 'excerpt', 'author'],
        'menu_icon'           => 'dashicons-rss',
    ]);
});
