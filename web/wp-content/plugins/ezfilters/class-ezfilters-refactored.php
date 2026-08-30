<?php

class EZFilters
{
    private $config;
    private $query;
    private $url;
    private $renderer;
    private $ajax;
    private $wpml;
    
    private $fields;
    private $datas = [];
    private $isWPML;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        // Initialisation des modules dans l'ordre de dépendance
        $this->config = new EZFiltersConfig();
        $this->query = new EZFiltersQuery($this->config);
        $this->wpml = new EZFiltersWPML($this->config, $this->query);
        $this->url = new EZFiltersUrl($this->config, $this->wpml->isWPML());
        $this->renderer = new EZFiltersRenderer($this->config);
        $this->ajax = new EZFiltersAjax($this->config, $this->query, $this->url, $this->renderer, $this->wpml);
        
        // Synchroniser les propriétés pour la compatibilité
        $this->syncLegacyProperties();
        
        // Initialisation des callbacks personnalisés
        $this->customCallback();
    }

    /**
     * Synchronise les propriétés pour maintenir la compatibilité avec l'ancienne API
     */
    private function syncLegacyProperties()
    {
        // Hook pour s'assurer que les données sont chargées
        add_action('init', function() {
            $this->fields = $this->config->getFields();
            $this->datas = $this->config->getDatas();
            $this->isWPML = $this->wpml->isWPML();
        }, 20); // Priorité plus tard pour s'assurer que les données sont prêtes
    }

    // Méthodes publiques pour l'API
    public function render($post_type = false)
    {
        return $this->renderer->render($post_type);
    }

    public function renderFilters($post_type = false)
    {
        return $this->renderer->renderFilters($post_type);
    }

    public function renderList($post_type = false, $args = [])
    {
        return $this->renderer->renderList($post_type, $args);
    }

    public function renderPaging($args = [])
    {
        return $this->renderer->renderPaging($args);
    }

    public function getFilteredArchiveLink($post_type, $filters = [])
    {
        return $this->url->getFilteredArchiveLink($post_type, $filters);
    }

    public function getCurrentSearch()
    {
        return $this->query->getCurrentSearch();
    }

    public function isCurrentSearchEmpty()
    {
        return $this->query->isCurrentSearchEmpty();
    }

    public function getQuery($query)
    {
        return $this->query->buildQuery($query);
    }

    public function getTranslatedUrl($url, $lang_code, $post_type = false)
    {
        return $this->wpml->getTranslatedUrl($url, $lang_code, $post_type);
    }

    // Méthodes pour accéder aux modules (si nécessaire)
    public function getConfig()
    {
        return $this->config;
    }

    public function getQueryModule()
    {
        return $this->query;
    }

    public function getUrlModule()
    {
        return $this->url;
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function getWPMLModule()
    {
        return $this->wpml;
    }

    // Méthodes de compatibilité avec l'ancienne API
    public function getPostType()
    {
        return EZFiltersUtils::getPostType();
    }

    /**
     * Getter pour les datas (compatibilité)
     * @return array
     */
    public function getDatas()
    {
        return $this->config->getDatas();
    }

    /**
     * Getter pour les fields (compatibilité)
     * @return array
     */
    public function getFields()
    {
        return $this->config->getFields();
    }

    /**
     * Magic getter pour l'accès aux propriétés (compatibilité avancée)
     */
    public function __get($property)
    {
        switch ($property) {
            case 'datas':
                return $this->config->getDatas();
            case 'fields':
                return $this->config->getFields();
            case 'isWPML':
                return $this->wpml->isWPML();
            default:
                if (property_exists($this, $property)) {
                    return $this->$property;
                }
                return null;
        }
    }

    /**
     * Magic setter pour la compatibilité
     */
    public function __set($property, $value)
    {
        switch ($property) {
            case 'datas':
            case 'fields':
            case 'isWPML':
                // Ces propriétés sont en lecture seule dans la nouvelle architecture
                trigger_error("Property {$property} is read-only in the refactored version", E_USER_WARNING);
                break;
            default:
                $this->$property = $value;
        }
    }

    /**
     * Magic isset pour la compatibilité
     */
    public function __isset($property)
    {
        switch ($property) {
            case 'datas':
                return !empty($this->config->getDatas());
            case 'fields':
                return !empty($this->config->getFields());
            case 'isWPML':
                return $this->wpml->isWPML() !== null;
            default:
                return isset($this->$property);
        }
    }

    // Callback personnalisé pour les templates
    private function customCallback()
    {
    }
} 