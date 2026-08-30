<?php

class EZFiltersRenderer
{
    private $config;
    private $template_dir;
    private $template_path = 'template-parts/ezfilters';

    public function __construct($config)
    {
        $this->config = $config;
        $this->template_dir = get_template_directory() . '/' . $this->template_path;
    }

    public function render($post_type = false)
    {
        if (empty($post_type)) {
            $post_type = EZFiltersUtils::getPostType();
        }

        $this->renderFilters($post_type);
        $this->renderList($post_type);
    }

    public function renderFilters($post_type = false)
    {
        if (empty($post_type)) {
            $post_type = EZFiltersUtils::getPostType();
        }

        $datas = $this->config->getDatas();
        if (!isset($datas[$post_type])) {
            return;
        }

        if (file_exists($this->template_dir . '/filters-' . $post_type . '.php')) {
            return get_template_part($this->template_path . '/filters-' . $post_type);
        } elseif (file_exists($this->template_dir . '/filters.php')) {
            return get_template_part($this->template_path . '/filters');
        }

        return get_template_part(str_replace(get_template_directory() . '/', '', __DIR__ . '/../') . 'template-parts/filters.sample');
    }

    public function renderList($post_type = false, $args = [])
    {
        if (empty($post_type)) {
            $post_type = EZFiltersUtils::getPostType();
        }

        $datas = $this->config->getDatas();
        if (!isset($datas[$post_type])) {
            return;
        }

        if (file_exists($this->template_dir . '/list-' . $post_type . '.php')) {
            return get_template_part($this->template_path . '/list-' . $post_type, null, $args);
        } elseif (file_exists($this->template_dir . '/list.php')) {
            return get_template_part($this->template_path . '/list', null, $args);
        }

        return get_template_part(str_replace(get_template_directory() . '/', '', __DIR__ . '/../') . 'template-parts/list.sample', null, $args);
    }

    public function renderPaging($args = [])
    {
        $post_type = EZFiltersUtils::getPostType();

        if (file_exists($this->template_dir . '/paging-' . $post_type . '.php')) {
            return get_template_part($this->template_path . '/paging-' . $post_type, null, $args);
        } elseif (file_exists($this->template_dir . '/paging.php')) {
            return get_template_part($this->template_path . '/paging', null, $args);
        }

        return get_template_part(str_replace(get_template_directory() . '/', '', __DIR__ . '/../') . 'template-parts/paging.sample', null, $args);
    }

    public function renderListForAjax($post_type, $query, $url)
    {
        ob_start();
        
        $this->renderList($post_type, [
            'query' => $query,
            'url' => $url
        ]);
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
} 