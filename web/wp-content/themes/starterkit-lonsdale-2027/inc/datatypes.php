<?php

add_action('init', function () {
    $content_types = [
        'news' => [
            'singular' => 'News',
            'plural' => 'News',
            'rewrite' => 'news',
            'menu_icon' => 'dashicons-rss',
            'supports' => ['title', 'thumbnail', 'excerpt', 'author'],
            'card' => 'news',
        ],
        'project' => [
            'singular' => 'Project',
            'plural' => 'Projects',
            'rewrite' => 'projects',
            'menu_icon' => 'dashicons-portfolio',
            'supports' => ['title', 'thumbnail', 'excerpt'],
            'card' => 'project',
        ],
        'expertise' => [
            'singular' => 'Expertise',
            'plural' => 'Expertises',
            'rewrite' => 'expertises',
            'menu_icon' => 'dashicons-admin-tools',
            'supports' => ['title', 'thumbnail', 'excerpt'],
        ],
        'team' => [
            'singular' => 'Member',
            'plural' => 'Team',
            'rewrite' => 'team',
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'thumbnail', 'excerpt'],
            'card' => 'team',
        ],
        'testimonial' => [
            'singular' => 'Testimonial',
            'plural' => 'Testimonials',
            'rewrite' => 'testimonials',
            'menu_icon' => 'dashicons-format-quote',
            'supports' => ['title', 'thumbnail', 'excerpt'],
        ],
    ];

    foreach ($content_types as $post_type => $definition) {
        $singular = $definition['singular'];
        $plural = $definition['plural'];

        register_post_type($post_type, [
            'label' => __($plural),
            'labels' => [
                'name' => __($plural),
                'singular_name' => __($singular),
                'add_new' => __('Ajouter'),
                'add_new_item' => sprintf(__('Ajouter : %s'), $singular),
                'edit_item' => sprintf(__('Modifier : %s'), $singular),
                'new_item' => sprintf(__('Nouveau : %s'), $singular),
                'view_item' => sprintf(__('Voir : %s'), $singular),
                'search_items' => sprintf(__('Rechercher dans %s'), $plural),
                'not_found' => sprintf(__('Aucun contenu trouvé dans %s'), $plural),
                'not_found_in_trash' => sprintf(__('Aucun contenu dans la corbeille pour %s'), $plural),
                'all_items' => sprintf(__('Tous : %s'), $plural),
                'menu_name' => __($plural),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'has_archive' => $definition['rewrite'],
            'rewrite' => [
                'slug' => $definition['rewrite'],
                'with_front' => false,
            ],
            'query_var' => true,
            'supports' => $definition['supports'],
            'menu_icon' => $definition['menu_icon'],
        ]);
    }

    add_filter('theme_content_type_card', static function ($card, $post_type) use ($content_types) {
        $configured_card = $content_types[$post_type]['card'] ?? '';
        return is_string($configured_card) && $configured_card !== '' ? $configured_card : $card;
    }, 10, 2);

    $taxonomies = [
        'news_category' => [
            'singular' => 'Catégorie d’actualité',
            'plural' => 'Catégories d’actualités',
            'rewrite' => 'categories-actualites',
            'object_types' => ['news'],
        ],
        'project_type' => [
            'singular' => 'Type de projet',
            'plural' => 'Types de projets',
            'rewrite' => 'types-projets',
            'object_types' => ['project'],
        ],
        'sector' => [
            'singular' => 'Secteur',
            'plural' => 'Secteurs',
            'rewrite' => 'secteurs',
            'object_types' => ['project'],
        ],
        'team_role' => [
            'singular' => 'Role',
            'plural' => 'Roles',
            'rewrite' => 'roles',
            'object_types' => ['team'],
        ],
    ];

    foreach ($taxonomies as $taxonomy => $definition) {
        $singular = $definition['singular'];
        $plural = $definition['plural'];

        register_taxonomy($taxonomy, $definition['object_types'], [
            'labels' => [
                'name' => __($plural),
                'singular_name' => __($singular),
                'search_items' => sprintf(__('Rechercher dans %s'), $plural),
                'all_items' => sprintf(__('Tous : %s'), $plural),
                'edit_item' => sprintf(__('Modifier : %s'), $singular),
                'update_item' => sprintf(__('Mettre à jour : %s'), $singular),
                'add_new_item' => sprintf(__('Ajouter : %s'), $singular),
                'new_item_name' => sprintf(__('Nouveau : %s'), $singular),
                'menu_name' => __($plural),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => $definition['rewrite'],
                'with_front' => false,
            ],
            'query_var' => true,
        ]);
    }
});
