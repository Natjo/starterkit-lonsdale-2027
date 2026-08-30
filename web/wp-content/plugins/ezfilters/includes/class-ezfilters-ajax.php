<?php

class EZFiltersAjax
{
    private $config;
    private $query;
    private $url;
    private $renderer;
    private $wpml;

    public function __construct($config, $query, $url, $renderer, $wpml = null)
    {
        $this->config = $config;
        $this->query = $query;
        $this->url = $url;
        $this->renderer = $renderer;
        $this->wpml = $wpml;
        $this->init();
    }

    private function init()
    {
        add_action("wp_ajax_ezfilters_search", [$this, "ajaxCallback"]);
        add_action("wp_ajax_nopriv_ezfilters_search", [$this, "ajaxCallback"]);
        add_action('wp_head', [$this, 'printNonce'], 1);
    }

    public function printNonce()
    {
        echo '<script>var ezfilters_nonce = "' . wp_create_nonce('ezfilters_ajax') . '";</script>';
    }

    public function ajaxCallback()
    {
        check_ajax_referer('ezfilters_ajax', 'nonce');
        $response = [];
        $post_type = EZFiltersUtils::sanitizePostData('post_type', 'post');
        $paged = EZFiltersUtils::validatePaged(EZFiltersUtils::sanitizePostData('paged', 1));
        $isLoadMore = EZFiltersUtils::sanitizePostData('is_load_more', false);

        $datas = $this->buildFilterDatas($post_type);
        $url = $this->url->buildAjaxUrl($post_type, $datas, $isLoadMore ? 1 : $paged);

        $query = $this->query->buildQuery(new WP_Query());
        $query = new WP_Query($query->query_vars);

        $response['html'] = $this->renderer->renderListForAjax($post_type, $query, $url);
        $response['url'] = $url;
        $response['paged'] = $paged;
        $response['found_posts'] = $query->found_posts;
        $response['max_num_pages'] = $query->max_num_pages;

        if ($this->wpml && $this->wpml->isWPML()) {
            $response['hreflangs'] = $this->buildHreflangs($url, $post_type);
        }

        wp_send_json($response);
    }

    private function buildFilterDatas($post_type)
    {
        $datas = [];
        $config_datas = $this->config->getDatas();

        foreach ($config_datas[$post_type]['filters'] as $slug => $filter) {
            $datas[$slug] = EZFiltersUtils::sanitizePostData($slug, []);
        }

        return $datas;
    }

    private function buildHreflangs($url, $post_type)
    {
        $hreflangs = [];
        $languages = apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc');

        foreach ($languages as $key => $language) {
            $hreflangs[$key] = $this->wpml->getTranslatedUrl($url, $language['code'], $post_type);
        }

        return $hreflangs;
    }
}
