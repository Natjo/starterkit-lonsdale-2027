<?php

class EZFiltersWPML
{
    private $config;
    private $query;
    private $isWPML;

    public function __construct($config, $query)
    {
        $this->config = $config;
        $this->query = $query;
        $this->setIsWPML();
        $this->init();
    }

    private function init()
    {
        if ($this->isWPML) {
            $this->updateHreflangs();
            $this->addWPMLFilters();
        }
    }

    private function setIsWPML()
    {
        $this->isWPML = defined('ICL_LANGUAGE_CODE');
    }

    public function isWPML()
    {
        return $this->isWPML;
    }

    private function addWPMLFilters()
    {
        add_filter('acf/settings/current_language', function () {
            return apply_filters('wpml_current_language', null);
        });
    }

    public function getTranslatedUrl($url, $lang_code, $post_type = false)
    {
        if (!$this->isWPML) {
            return $url;
        }

        global $sitepress;

        if (!empty($post_type) || ((is_archive() || is_home()) && !is_tax() && !is_category() && !is_tag())) {

            if (empty($post_type)) {
                $post_type = get_post_type();
                if (empty($post_type)) $post_type = 'post';
            }

            $current_lang = $sitepress->get_current_language();
            $sitepress->switch_lang($lang_code, true);
            $url = get_post_type_archive_link($post_type);
            $sitepress->switch_lang($current_lang, true);
        }

        $isCurrentSearchEmpty = $this->query->isCurrentSearchEmpty();
        if (!$isCurrentSearchEmpty) {
            $url = $this->translateFilteredUrl($url, $lang_code, $post_type);
        }

        return $url;
    }

    private function translateFilteredUrl($url, $lang_code, $post_type)
    {
        global $sitepress;
        
        $current_lang = $sitepress->get_current_language();
        $currentSearch = $this->query->getCurrentSearch();

        if (substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

        $terms_to_translate = $this->collectTermsToTranslate($currentSearch);
        
        if ($url === false) {
            return false;
        }

        $translated_search = $this->translateTerms($terms_to_translate, $lang_code, $currentSearch);
        
        if ($url !== false) {
            $url = $this->buildTranslatedUrl($url, $translated_search, $lang_code, $post_type);
        }

        $sitepress->switch_lang($current_lang, true);
        return $url;
    }

    private function collectTermsToTranslate($currentSearch)
    {
        $terms_to_translate = [];
        
        foreach ($currentSearch as $key => $value) {
            foreach ($value as $k => $v) {
                $term = get_term_by('slug', $v, $key);
                if ($term) {
                    $terms_to_translate[] = ['term_id' => $term->term_id, 'taxonomy' => $key, 'key' => $key, 'index' => $k];
                } else {
                    return false;
                }
            }
        }

        return $terms_to_translate;
    }

    private function translateTerms($terms_to_translate, $lang_code, &$currentSearch)
    {
        global $sitepress;
        
        foreach ($terms_to_translate as $term_data) {
            $translated_term_id = apply_filters('wpml_object_id', $term_data['term_id'], $term_data['taxonomy'], false, $lang_code);
            if (!$translated_term_id) {
                continue;
            }

            $sitepress->switch_lang($lang_code, true);
            $translated_term = get_term_by('id', $translated_term_id, $term_data['taxonomy']);

            if ($translated_term) {
                $currentSearch[$term_data['key']][$term_data['index']] = $translated_term->slug;
            } else {
                return false;
            }
        }

        return $currentSearch;
    }

    private function buildTranslatedUrl($url, $currentSearch, $lang_code, $post_type)
    {
        global $sitepress;
        
        $i = 0;
        $sitepress->switch_lang($lang_code, true);
        $fields = get_field('params-ezfilters', 'options');

        foreach ($currentSearch as $key => $value) {
            if (!empty($value)) {
                if ($i == 0 && !empty($fields['url_separator'])) {
                    $url .= '/' . $fields['url_separator'];
                }
                
                foreach ($fields['items'] as $item) {
                    if ($item['post_type'] == $post_type) {
                        foreach ($item['filters'] as $filter) {
                            if ($key == $filter['taxonomy'] || $key == $filter['field']) {
                                $url .= '/' . $filter['url'] . '/' . implode(',', $value);
                                break 2;
                            }
                        }
                    }
                }
                $i++;
            }
        }

        return $url;
    }

    private function updateHreflangs()
    {
        add_filter('wpml_alternate_hreflang', function ($url, $lang_code) {
            return $this->getTranslatedUrl($url, $lang_code);
        }, 10, 2);
    }
} 