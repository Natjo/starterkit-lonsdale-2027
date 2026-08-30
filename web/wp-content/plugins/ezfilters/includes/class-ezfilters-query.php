<?php

class EZFiltersQuery
{
    private $config;
    private $url_separator = ",";
    private $currentSearchCache = null;

    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    private function init()
    {
        $this->generateQueryVars();
        $this->updatePreGetPosts();
    }

    public function getCurrentSearch()
    {
        if ($this->currentSearchCache !== null) {
            return $this->currentSearchCache;
        }

        $currentSearch = [];
        $datas = $this->config->getDatas();

        foreach ($datas as $post_type => $data) {
            foreach ($data['filters'] as $slug => $filter) {
                $query_var = get_query_var('ezfilters_' . $slug);

                if (wp_doing_ajax()) {
                    $query_var = EZFiltersUtils::sanitizePostData($slug, []);
                    set_query_var('ezfilters_' . $slug, $query_var);
                }

                if(!empty($query_var) && is_array($query_var) && empty($query_var[0])) {
                    unset($query_var[0]);
                }

                if ($filter['type'] == 'keywords') {
                    $currentSearch[$slug] = !empty($query_var) ? urldecode(filter_var(strip_tags($query_var), FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : [];
                } else {
                    $currentSearch[$slug] = !empty($query_var) ? $query_var : [];
                    if (!is_array($currentSearch[$slug])) {
                        $currentSearch[$slug] = explode($this->url_separator, $currentSearch[$slug]);
                    }
                }

                $get_value = EZFiltersUtils::sanitizeGetData($slug);
                if (empty($currentSearch[$slug]) && !empty($get_value)) {
                    $currentSearch[$slug] = $get_value;

                    if ($filter['type'] !== "keywords") {
                        $currentSearch[$slug] = !is_array($currentSearch[$slug]) ? explode($this->url_separator, $currentSearch[$slug]) : $currentSearch[$slug];

                        // Optimisation : traiter tous les termes d'un coup
                        if (!empty($currentSearch[$slug])) {
                            $currentSearch[$slug] = $this->optimizeTermsLookup($currentSearch[$slug], $slug);
                        }
                    }
                }
            }
        }

        $this->currentSearchCache = $currentSearch;
        return $currentSearch;
    }

    public function resetSearchCache()
    {
        $this->currentSearchCache = null;
    }

    private function optimizeTermsLookup($values, $taxonomy)
    {
        $numeric_values = array_filter($values, 'is_numeric');
        if (!empty($numeric_values)) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'include' => $numeric_values,
                'hide_empty' => false
            ]);
            
            $terms_by_id = [];
            foreach ($terms as $term) {
                $terms_by_id[$term->term_id] = $term->slug;
            }
            
            foreach ($values as $key => $value) {
                if (is_numeric($value) && isset($terms_by_id[$value])) {
                    $values[$key] = $terms_by_id[$value];
                }
            }
        }

        return $values;
    }

    public function isCurrentSearchEmpty()
    {
        $currentSearch = $this->getCurrentSearch();
        return !array_filter($currentSearch);
    }

    private function generateQueryVars()
    {
        add_filter('query_vars', function ($query_vars) {
            $datas = $this->config->getDatas();
            foreach ($datas as $post_type => $data) {
                foreach ($data['filters'] as $slug => $filter) {
                    $query_vars[] = 'ezfilters_' . $slug;
                }
            }

            return $query_vars;
        });
    }

    private function updatePreGetPosts()
    {
        add_action('pre_get_posts', function ($query) {
            if (
                $query->is_main_query() && !is_admin() && 
                (is_archive() || is_search() || is_front_page() || is_home())
            ) {
                $obj = get_queried_object();
                $paged = get_query_var('paged') ?: 1;

                $post_type = '';
                if (is_array($query->query)) {
                    $post_type = $query->query['post_type'] ?? '';
                } else {
                    $post_type = $query->query->post_type ?? '';
                }

                if (empty($post_type)) $post_type = 'post';
                if (is_array($post_type)) $post_type = $post_type[0];

                $datas = $this->config->getDatas();
                if (!isset($datas[$post_type])) {
                    return;
                }

                if (is_tax() || is_category() || is_tag()) {
                    set_query_var('ezfilters_' . $obj->taxonomy, $obj->slug);
                }

                $query = $this->buildQuery($query);
            }
        });
    }

    public function buildQuery($query) 
    {
        if (wp_doing_ajax()) {
            $post_type = EZFiltersUtils::sanitizePostData('post_type', 'post');
            $paged = EZFiltersUtils::validatePaged(EZFiltersUtils::sanitizePostData('paged', 1));
            set_query_var('paged', $paged);
        } else {
            $paged = get_query_var('paged');
            
            if (is_array($query->query)) {
                $post_type = $query->query['post_type'] ?? '';
            } else {
                $post_type = $query->query->post_type ?? '';
            }
        }
        
        $currentSearch = $this->getCurrentSearch();
        $isCurrentSearchEmpty = $this->isCurrentSearchEmpty();

        $taxquery = [];
        $datas = $this->config->getDatas();
        $fields = $this->config->getFields();

        foreach ($currentSearch as $slug => $values) {
            if (!empty($values)) {
                switch ($datas[$post_type]['filters'][$slug]['type']) {
                    case 'keywords':
                        $query->set('s', $values);
                        break;

                    case 'acf':
                        if ($datas[$post_type]['filters'][$slug]['field'] == "solution") {
                            $values = $this->processEntityValues($values);
                        }

                        $meta_query = [
                            'relation' => 'OR',
                        ];

                        foreach ($values as $value) {
                            $meta_query[] = array(
                                'key' => 'solution',
                                'value' => $value,
                                'compare' => 'LIKE'
                            );
                        }

                        $query->set('meta_query', $meta_query);
                        break;

                    case 'taxonomy':
                        if (empty($taxquery)) {
                            $taxquery['relation'] = $fields['operator'];
                        }

                        $taxquery[] = [
                            'taxonomy' => $slug,
                            'field' => 'slug',
                            'terms' => $values,
                            'operator' => 'IN'
                        ];
                        break;

                    case 'relation':
                        $relation_post_type = $datas[$post_type]['filters'][$slug]['relation'] ?? '';
                        $relation_ids = $this->getRelationPostIds($values, $relation_post_type);

                        if (!empty($relation_ids)) {
                            $meta_query = ['relation' => 'OR'];
                            foreach ($relation_ids as $id) {
                                $meta_query[] = [
                                    'key' => $slug,
                                    'value' => $id,
                                    'compare' => '='
                                ];
                            }
                            $query->set('meta_query', $meta_query);
                        }
                        break;
                }
            }
        }

        if (!empty($taxquery)) {
            $query->set('tax_query', $taxquery);
        }

        $query->set('posts_per_page', $datas[$post_type]['posts_per_page'] ?? 12);
        $query->set('post_type', $post_type);
        $query->set('paged', $paged);
        $query->set('post_status', 'publish');
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');

        return $query;
    }

    private function getRelationPostIds($values, $relation_post_type)
    {
        if (empty($values) || empty($relation_post_type)) {
            return [];
        }

        $posts = get_posts([
            'post_type' => $relation_post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post_name__in' => $values,
            'fields' => 'ids',
        ]);

        return $posts;
    }

    private function processEntityValues($values)
    {
        $post_names = array_filter($values);
        if (!empty($post_names)) {
            $posts = get_posts([
                "post_type" => "solution",
                "post_status" => "publish",
                "posts_per_page" => -1,
                "meta_query" => [
                    "relation" => "OR",
                    [
                        "key" => "is_landing",
                        "compare" => "NOT EXISTS",
                    ],
                    [
                        "key" => "is_landing",
                        "value" => "1",
                        "compare" => "!=",
                    ],
                ],
            ]);
            
            $post_ids = [];
            foreach ($posts as $post) {
                if (in_array($post->post_name, $values)) {
                    $post_ids[] = $post->ID;
                }
            }
            return $post_ids;
        }
        return $values;
    }
} 