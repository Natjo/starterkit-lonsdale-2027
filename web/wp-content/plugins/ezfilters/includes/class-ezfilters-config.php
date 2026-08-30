<?php

class EZFiltersConfig
{
    private $excluded_post_types = ['page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'wp_font_family', 'wp_font_face', 'acf-taxonomy', 'acf-post-type', 'acf-ui-options-page', 'acf-field-group', 'acf-field'];
    private $excluded_taxonomies = ['nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area', 'wp_pattern_category'];
    
    public $fields;
    public $datas = [];
    
    private $terms_cache = [];
    private $relations_cache = [];

    public function __construct()
    {
        $this->addAcfOptionsPage();
        $this->addAcfFields();
        $this->setFields();
        $this->setDatas();
    }

    public function addAcfOptionsPage()
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }

        acf_add_options_page([
            'page_title'    => __('EZFilters'),
            'menu_title'    => __('EZFilters'),
            'parent_slug'   => 'options-general.php',
        ]);

        add_action('acf/options_page/save', function ($post_id, $menu_slug) {
            if ($menu_slug == "acf-options-ezfilters") {
                flush_rewrite_rules();
            }
        }, 10, 2);
    }

    public function addAcfFields()
    {
        add_action('init', function () {
            if (!function_exists('acf_add_local_field_group')) {
                return;
            }

            acf_add_local_field_group(array(
                'key' => 'group_6776a4339ea11',
                'title' => 'Options - EZFilters',
                'fields' => array(
                    array(
                        'key' => 'field_6777f29f873d3',
                        'label' => '',
                        'name' => '',
                        'aria-label' => '',
                        'type' => 'message',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => 'N\'oubliez pas de regénérer les <a href="/wp-admin/options-permalink.php">permaliens</a> après chaque modification.',
                        'new_lines' => 'wpautop',
                        'esc_html' => 0,
                    ),
                    array(
                        'key' => 'field_6777f5ee5e31f',
                        'label' => 'Filtres',
                        'name' => 'params-ezfilters',
                        'aria-label' => '',
                        'type' => 'group',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'layout' => 'block',
                        'sub_fields' => array(
                            array(
                                'key' => 'field_6777f63e5e320',
                                'label' => 'Séparateur d\'url',
                                'name' => 'url_separator',
                                'aria-label' => '',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array(
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'default_value' => 'filtres',
                                'maxlength' => '',
                                'allow_in_bindings' => 0,
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                            ),
                            array(
                                'key' => 'field_6778081c4be05',
                                'label' => 'Opérateur de recherche',
                                'name' => 'operator',
                                'aria-label' => '',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 1,
                                'conditional_logic' => 0,
                                'wrapper' => array(
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'choices' => array(
                                    'AND' => 'ET',
                                    'OR' => 'OU',
                                ),
                                'default_value' => false,
                                'return_format' => 'value',
                                'multiple' => 0,
                                'allow_null' => 0,
                                'allow_in_bindings' => 0,
                                'ui' => 0,
                                'ajax' => 0,
                                'placeholder' => '',
                            ),
                            array(
                                'key' => 'field_6776a466d370f',
                                'label' => 'Recherches',
                                'name' => 'items',
                                'aria-label' => '',
                                'type' => 'repeater',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array(
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'layout' => 'block',
                                'pagination' => 0,
                                'min' => 0,
                                'max' => 0,
                                'collapsed' => '',
                                'button_label' => 'Ajouter une recherche',
                                'rows_per_page' => 20,
                                'sub_fields' => array(
                                    array(
                                        'key' => 'field_6776a489c5b5d',
                                        'label' => 'Type de post',
                                        'name' => 'post_type',
                                        'aria-label' => '',
                                        'type' => 'select',
                                        'instructions' => '',
                                        'required' => 1,
                                        'conditional_logic' => 0,
                                        'wrapper' => array(
                                            'width' => '',
                                            'class' => '',
                                            'id' => '',
                                        ),
                                        'choices' => array(
                                            'post' => 'Articles',
                                            'event' => 'Event',
                                        ),
                                        'default_value' => false,
                                        'return_format' => 'value',
                                        'multiple' => 0,
                                        'allow_null' => 0,
                                        'allow_in_bindings' => 0,
                                        'ui' => 0,
                                        'ajax' => 0,
                                        'placeholder' => '',
                                        'parent_repeater' => 'field_6776a466d370f',
                                    ),
                                    array(
                                        'key' => 'field_6776a49bc5b5e',
                                        'label' => 'Éléments par page',
                                        'name' => 'posts_per_page',
                                        'aria-label' => '',
                                        'type' => 'number',
                                        'instructions' => '',
                                        'required' => 1,
                                        'conditional_logic' => 0,
                                        'wrapper' => array(
                                            'width' => '',
                                            'class' => '',
                                            'id' => '',
                                        ),
                                        'default_value' => 12,
                                        'min' => -1,
                                        'max' => '',
                                        'allow_in_bindings' => 0,
                                        'placeholder' => '',
                                        'step' => '',
                                        'prepend' => '',
                                        'append' => '',
                                        'parent_repeater' => 'field_6776a466d370f',
                                    ),
                                    array(
                                        'key' => 'field_6776a4c4c5b5f',
                                        'label' => 'Filtres',
                                        'name' => 'filters',
                                        'aria-label' => '',
                                        'type' => 'repeater',
                                        'instructions' => '',
                                        'required' => 0,
                                        'conditional_logic' => 0,
                                        'wrapper' => array(
                                            'width' => '',
                                            'class' => '',
                                            'id' => '',
                                        ),
                                        'layout' => 'block',
                                        'min' => 0,
                                        'max' => 0,
                                        'collapsed' => '',
                                        'button_label' => 'Ajouter un filtre',
                                        'rows_per_page' => 20,
                                        'sub_fields' => array(
                                            array(
                                                'key' => 'field_6776a7f9ec518',
                                                'label' => 'Type',
                                                'name' => 'type',
                                                'aria-label' => '',
                                                'type' => 'select',
                                                'instructions' => '',
                                                'required' => 1,
                                                'conditional_logic' => 0,
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'choices' => array(
                                                    'keywords' => 'Mots-clés',
                                                    'taxonomy' => 'Taxonomie',
                                                    'relation' => 'Relation',
                                                    'acf' => 'ACF',
                                                ),
                                                'default_value' => false,
                                                'return_format' => 'value',
                                                'multiple' => 0,
                                                'allow_null' => 0,
                                                'allow_in_bindings' => 0,
                                                'ui' => 0,
                                                'ajax' => 0,
                                                'placeholder' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6776a98a01fb6',
                                                'label' => 'Titre',
                                                'name' => 'title',
                                                'aria-label' => '',
                                                'type' => 'text',
                                                'instructions' => '',
                                                'required' => 1,
                                                'conditional_logic' => 0,
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'default_value' => '',
                                                'maxlength' => '',
                                                'allow_in_bindings' => 0,
                                                'placeholder' => '',
                                                'prepend' => '',
                                                'append' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6776a99b01fb7',
                                                'label' => 'Url',
                                                'name' => 'url',
                                                'aria-label' => '',
                                                'type' => 'text',
                                                'instructions' => '',
                                                'required' => 0,
                                                'conditional_logic' => 0,
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'default_value' => '',
                                                'maxlength' => '',
                                                'allow_in_bindings' => 0,
                                                'placeholder' => '',
                                                'prepend' => '',
                                                'append' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6776a9fa01fb8',
                                                'label' => 'Champ',
                                                'name' => 'field',
                                                'aria-label' => '',
                                                'type' => 'text',
                                                'instructions' => '',
                                                'required' => 1,
                                                'conditional_logic' => array(
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'acf',
                                                        ),
                                                    ),
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'relation',
                                                        ),
                                                    ),
                                                ),
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'default_value' => '',
                                                'maxlength' => '',
                                                'allow_in_bindings' => 0,
                                                'placeholder' => '',
                                                'prepend' => '',
                                                'append' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6776aa9de1ced',
                                                'label' => 'Taxonomie',
                                                'name' => 'taxonomy',
                                                'aria-label' => '',
                                                'type' => 'select',
                                                'instructions' => '',
                                                'required' => 1,
                                                'conditional_logic' => array(
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'taxonomy',
                                                        ),
                                                    ),
                                                ),
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'choices' => array(
                                                    'category' => 'Catégories',
                                                    'post_tag' => 'Étiquettes',
                                                    'translation_priority' => 'Priorités de traduction',
                                                    'location' => 'Location',
                                                ),
                                                'default_value' => false,
                                                'return_format' => 'value',
                                                'multiple' => 0,
                                                'allow_null' => 0,
                                                'allow_in_bindings' => 0,
                                                'ui' => 0,
                                                'ajax' => 0,
                                                'placeholder' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6776ab1de2def',
                                                'label' => 'Post Type',
                                                'name' => 'relation',
                                                'aria-label' => '',
                                                'type' => 'select',
                                                'instructions' => '',
                                                'required' => 1,
                                                'conditional_logic' => array(
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'relation',
                                                        ),
                                                    ),
                                                ),
                                                'wrapper' => array(
                                                    'width' => '17',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'choices' => array(),
                                                'default_value' => false,
                                                'return_format' => 'value',
                                                'multiple' => 0,
                                                'allow_null' => 0,
                                                'allow_in_bindings' => 0,
                                                'ui' => 0,
                                                'ajax' => 0,
                                                'placeholder' => '',
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_67780572bd7ff',
                                                'label' => 'Choix multiple',
                                                'name' => 'multiple_choice',
                                                'aria-label' => '',
                                                'type' => 'true_false',
                                                'instructions' => '',
                                                'required' => 0,
                                                'conditional_logic' => array(
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'taxonomy',
                                                        ),
                                                    ),
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'acf',
                                                        ),
                                                    ),
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'relation',
                                                        ),
                                                    ),
                                                ),
                                                'wrapper' => array(
                                                    'width' => '15',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'message' => '',
                                                'default_value' => 1,
                                                'allow_in_bindings' => 0,
                                                'ui_on_text' => '',
                                                'ui_off_text' => '',
                                                'ui' => 1,
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                            array(
                                                'key' => 'field_6777a3b3bc8ed',
                                                'label' => 'Données',
                                                'name' => 'datas',
                                                'aria-label' => '',
                                                'type' => 'repeater',
                                                'instructions' => '',
                                                'required' => 0,
                                                'conditional_logic' => array(
                                                    array(
                                                        array(
                                                            'field' => 'field_6776a7f9ec518',
                                                            'operator' => '==',
                                                            'value' => 'acf',
                                                        ),
                                                    ),
                                                ),
                                                'wrapper' => array(
                                                    'width' => '',
                                                    'class' => '',
                                                    'id' => '',
                                                ),
                                                'layout' => 'table',
                                                'min' => 0,
                                                'max' => 0,
                                                'collapsed' => '',
                                                'button_label' => 'Add Row',
                                                'rows_per_page' => 20,
                                                'sub_fields' => array(
                                                    array(
                                                        'key' => 'field_6777a3f1bc8ef',
                                                        'label' => 'Titre',
                                                        'name' => 'title',
                                                        'aria-label' => '',
                                                        'type' => 'text',
                                                        'instructions' => '',
                                                        'required' => 0,
                                                        'conditional_logic' => 0,
                                                        'wrapper' => array(
                                                            'width' => '',
                                                            'class' => '',
                                                            'id' => '',
                                                        ),
                                                        'default_value' => '',
                                                        'maxlength' => '',
                                                        'allow_in_bindings' => 0,
                                                        'placeholder' => '',
                                                        'prepend' => '',
                                                        'append' => '',
                                                        'parent_repeater' => 'field_6777a3b3bc8ed',
                                                    ),
                                                    array(
                                                        'key' => 'field_6777a3cdbc8ee',
                                                        'label' => 'Valeur',
                                                        'name' => 'value',
                                                        'aria-label' => '',
                                                        'type' => 'text',
                                                        'instructions' => '',
                                                        'required' => 0,
                                                        'conditional_logic' => 0,
                                                        'wrapper' => array(
                                                            'width' => '',
                                                            'class' => '',
                                                            'id' => '',
                                                        ),
                                                        'default_value' => '',
                                                        'maxlength' => '',
                                                        'allow_in_bindings' => 0,
                                                        'placeholder' => '',
                                                        'prepend' => '',
                                                        'append' => '',
                                                        'parent_repeater' => 'field_6777a3b3bc8ed',
                                                    ),
                                                ),
                                                'parent_repeater' => 'field_6776a4c4c5b5f',
                                            ),
                                        ),
                                        'parent_repeater' => 'field_6776a466d370f',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'acf-options-ezfilters',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
            ));
        });

        $this->addFieldFilters();
    }

    private function addFieldFilters()
    {
        add_action('init', function () {
            add_filter('acf/load_field/name=post_type', function ($field) {
                $field['choices'] = [];
                $items = get_post_types();

                foreach ($items as $item) {
                    if (!in_array($item, $this->excluded_post_types)) {
                        $item_object = get_post_type_object($item);
                        $field['choices'][$item_object->name] = $item_object->label;
                    }
                }

                return $field;
            });

            add_filter('acf/load_field/name=relation', function ($field) {
                $field['choices'] = [];
                $items = get_post_types();

                foreach ($items as $item) {
                    if (!in_array($item, $this->excluded_post_types)) {
                        $item_object = get_post_type_object($item);
                        $field['choices'][$item_object->name] = $item_object->label;
                    }
                }

                return $field;
            });

            add_filter('acf/load_field/name=taxonomy', function ($field) {
                $field['choices'] = [];
                $items = get_taxonomies();

                foreach ($items as $item) {
                    if (!in_array($item, $this->excluded_taxonomies)) {
                        $item_object = get_taxonomy($item);
                        $field['choices'][$item_object->name] = $item_object->label;
                    }
                }

                return $field;
            });
        });
    }

    public function setFields()
    {
        add_action('init', function () {
            $this->fields = get_field('params-ezfilters', 'options');
        });
    }

    public function setDatas()
    {
        add_action('init', function () {
            if (empty($this->fields) || empty($this->fields['items'])) {
                return;
            }

            // Pré-charger toutes les taxonomies nécessaires
            $taxonomies_to_load = [];
            $relations_to_load = [];
            foreach ($this->fields['items'] as $item) {
                if (isset($item['filters']) && !empty($item['filters'])) {
                    foreach ($item['filters'] as $filter) {
                        if ($filter['type'] == "taxonomy") {
                            $taxonomies_to_load[] = $filter['taxonomy'];
                        } elseif ($filter['type'] == "relation") {
                            $relations_to_load[] = $filter['relation'];
                        }
                    }
                }
            }

            // Charger tous les termes d'un coup
            if (!empty($taxonomies_to_load)) {
                $taxonomies_to_load = array_unique($taxonomies_to_load);
                foreach ($taxonomies_to_load as $taxonomy) {
                    if (!isset($this->terms_cache[$taxonomy])) {
                        $this->terms_cache[$taxonomy] = get_terms([
                            'taxonomy'   => $taxonomy,
                            'hide_empty' => false,
                            'exclude' => array(1),
                        ]);
                    }
                }
            }

            // Charger toutes les relations d'un coup
            if (!empty($relations_to_load)) {
                $relations_to_load = array_unique($relations_to_load);
                foreach ($relations_to_load as $relation) {
                    if (!isset($this->relations_cache[$relation])) {
                        $this->relations_cache[$relation] = get_posts([
                            "post_type" => $relation,
                            "post_status" => "publish",
                            "numberposts" => -1,
                            "orderby" => "title",
                            "order" => "ASC",
                            "suppress_filters" => false,
                        ]);
                    }
                }
            }

            foreach ($this->fields['items'] as $item) {
                $this->datas[$item['post_type']] = [
                    'posts_per_page' => $item['posts_per_page']
                ];

                if (isset($item['filters']) && !empty($item['filters'])) {
                    foreach ($item['filters'] as $filter) {
                        $slug = EZFiltersUtils::getFilterSlug($filter);
                        $this->datas[$item['post_type']]['filters'][$slug] = $filter;

                        if (empty($this->datas[$item['post_type']]['filters'][$slug]['url'])) {
                            $this->datas[$item['post_type']]['filters'][$slug]['url'] = $this->datas[$item['post_type']]['filters'][$slug]['title'];
                        }

                        $this->datas[$item['post_type']]['filters'][$slug]['url'] = EZFiltersUtils::toSlug($this->datas[$item['post_type']]['filters'][$slug]['url']);

                        $this->datas[$item['post_type']]['filters'][$slug]['name'] = $slug;

                        $this->processFilterData($item['post_type'], $slug, $filter);
                    }
                }
            }
        });
    }

    private function processFilterData($post_type, $slug, $filter)
    {
        if ($filter['type'] == "taxonomy") {
            $this->datas[$post_type]['filters'][$slug]['datas'] = [];
            
            // Utiliser le cache des termes
            $terms = $this->terms_cache[$slug] ?? [];

            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $this->datas[$post_type]['filters'][$slug]['datas'][] = [
                        'title' => $term->name,
                        'value' => $term->slug
                    ];
                }
            } else {
                unset($this->datas[$post_type]['filters'][$slug]);
            }
        } elseif ($filter['type'] == "relation") {
            $this->datas[$post_type]['filters'][$slug]['datas'] = [];

            $items = $this->relations_cache[$filter['relation']] ?? [];

            if (!empty($items)) {
                foreach ($items as $item) {
                    $this->datas[$post_type]['filters'][$slug]['datas'][] = [
                        'title' => $item->post_title,
                        'value' => $item->post_name,
                    ];
                }
            } else {
                unset($this->datas[$post_type]['filters'][$slug]);
            }
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getDatas()
    {
        return $this->datas;
    }
} 