<?php
/**
 * Autoloader pour EZFilters
 * 
 * Ce fichier charge automatiquement toutes les classes EZFilters
 * et initialise le système de filtres.
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes
define('EZFILTERS_PATH', __DIR__);
define('EZFILTERS_INCLUDES_PATH', EZFILTERS_PATH . '/includes');

/**
 * Fonction d'autoload pour les classes EZFilters
 */
function ezfilters_autoload($class_name) {
    // Vérifier si c'est une classe EZFilters
    if (strpos($class_name, 'EZFilters') !== 0) {
        return;
    }

    // Convertir le nom de classe en nom de fichier
    $class_file = strtolower(str_replace('EZFilters', 'ezfilters', $class_name));
    $class_file = str_replace('_', '-', $class_file);
    $file_path = EZFILTERS_INCLUDES_PATH . '/class-' . $class_file . '.php';

    // Charger le fichier s'il existe
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Enregistrer l'autoloader
spl_autoload_register('ezfilters_autoload');

// Charger les classes principales
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-utils.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-config.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-query.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-url.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-renderer.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-ajax.php';
require_once EZFILTERS_INCLUDES_PATH . '/class-ezfilters-wpml.php';

// Charger la classe principale
require_once EZFILTERS_PATH . '/class-ezfilters-refactored.php';

/**
 * Initialiser EZFilters
 * 
 * @return EZFilters Instance de la classe principale
 */
function ezfilters_init() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new EZFilters();
    }
    
    return $instance;
}

// Créer une instance globale après le chargement de tous les plugins (WPML définit ICL_LANGUAGE_CODE à plugins_loaded)
add_action('plugins_loaded', function () {
    global $ezfilters, $ezFilters;
    $ezfilters = ezfilters_init();
    $ezFilters = $ezfilters;
});

/**
 * Fonction helper pour accéder à l'instance EZFilters
 * 
 * @return EZFilters
 */
function ezfilters() {
    global $ezfilters;
    return $ezfilters;
} 