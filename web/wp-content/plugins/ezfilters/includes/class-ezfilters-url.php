<?php

class EZFiltersUrl
{
    private $config;
    private $url_separator = ",";
    private $isWPML;

    public function __construct($config, $isWPML = false)
    {
        $this->config = $config;
        $this->isWPML = $isWPML;
        $this->init();
    }

    private function init()
    {
        $this->generateRewriteRules();
    }

    public function getFilteredArchiveLink($post_type, $filters = [])
    {
        $url = get_post_type_archive_link($post_type);
        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

        $config_datas = $this->config->getDatas();
        $fields = $this->config->getFields();

        $i = 0;
        foreach ($filters as $key => $value) {
            if (empty($value)) continue;

            if ($i == 0 && !empty($fields['url_separator'])) {
                $url .= '/' . $fields['url_separator'];
            }

            $filter_url = isset($config_datas[$post_type]['filters'][$key]['url'])
                ? $config_datas[$post_type]['filters'][$key]['url']
                : $key;

            $value = is_array($value) ? implode(',', $value) : $value;
            $url .= '/' . $filter_url . '/' . $value;
            $i++;
        }

        return $url . '/';
    }

    private function generateRewriteRules()
    {
        add_action('init', function () {
            $languages = [1];
            $init_language = null;

            if ($this->isWPML) {
                $languages = apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc');
                $init_language = apply_filters('wpml_current_language', null);
            }

            foreach ($languages as $language) {
                if ($this->isWPML) {
                    do_action('wpml_switch_language', $language['code']);
                }

                $fields = get_field('params-ezfilters', 'options');
                if (empty($fields) || empty($fields['items'])) {
                    continue;
                }

                $processed_data = $this->processFieldsForLanguage($fields);
                $this->generateRulesForData($processed_data, $fields, $language);
            }

            if ($this->isWPML && $init_language) {
                do_action('wpml_switch_language', $init_language);
            }
        });
    }

    private function processFieldsForLanguage($fields)
    {
        $processed_data = [];
        foreach ($fields['items'] as $item) {
            $post_type = $item['post_type'];
            $processed_data[$post_type] = [
                'posts_per_page' => $item['posts_per_page'],
                'filters' => []
            ];

            if (isset($item['filters']) && !empty($item['filters'])) {
                foreach ($item['filters'] as $filter) {
                    $slug = EZFiltersUtils::getFilterSlug($filter);
                    $processed_data[$post_type]['filters'][$slug] = $filter;
                }
            }
        }

        return $processed_data;
    }

    private function generateRulesForData($processed_data, $fields, $language)
    {
        foreach ($processed_data as $post_type => $data) {
            $url = get_post_type_archive_link($post_type);
            $url = parse_url($url, PHP_URL_PATH) ?: '/';
            $url = ltrim($url, '/');

            if ($this->isWPML && str_starts_with($url, $language['language_code'] . '/')) {
                $url = substr($url, strlen($language['language_code']) + 1);
            }

            // Règle simple pour l'archive
            $regex = '^' . rtrim($url, '/') . '(?:\/page\/([\d]+))?\/?$';
            $query = 'index.php?post_type=' . $post_type . '&paged=$matches[1]';
            add_rewrite_rule($regex, $query, 'top');

            // Règles avec filtres
            $this->generateFilterRules($url, $post_type, $data, $fields);
        }
    }

    private function generateFilterRules($url, $post_type, $data, $fields)
    {
        $regex = '^' . $url;
        if (!empty($fields['url_separator'])) {
            $regex .= $fields['url_separator'];
        } else {
            $regex = rtrim($regex, '/');
        }

        $query = 'index.php?post_type=' . $post_type;
        $i = 1;

        if (!empty($data['filters'])) {
            foreach ($data['filters'] as $slug => $filter) {
                $regex .= '(?:\/' . $filter['url'] . '\/([^\/]+))?';
                $query .= '&ezfilters_' . $slug . '=$matches[' . $i . ']';
                $i++;
            }
        }

        $regex .= '(?:\/page\/([\d]+))?\/?$';
        $query .= '&paged=$matches[' . $i . ']';

        add_rewrite_rule($regex, $query, 'top');
    }

    public function buildAjaxUrl($post_type, $datas, $paged = 1)
    {
        $url = get_post_type_archive_link($post_type);
        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

        $config_datas = $this->config->getDatas();
        $fields = $this->config->getFields();

        $i = 0;
        foreach ($datas as $key => $value) {
            if (!empty($value)) {
                if ($i == 0 && !empty($fields['url_separator'])) {
                    $url .= "/" . $fields['url_separator'];
                }

                $url .= '/' . $config_datas[$post_type]['filters'][$key]['url'] . '/';
                $url .= is_array($value) ? implode($this->url_separator, $value) : $value;
                $i++;
            }
        }

        if ($paged > 1) {
            $url .= '/page/' . $paged;
        }

        if (substr($url, -1) !== '/') {
            $url .= '/';
        }

        return $url;
    }
} 