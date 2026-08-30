# EZFilters - Architecture Refactorisée

## Vue d'ensemble

Le code EZFilters a été refactorisé pour améliorer sa maintenabilité, sa lisibilité et sa performance. L'architecture monolithique initiale (1272 lignes dans un seul fichier) a été divisée en modules spécialisés suivant le principe de responsabilité unique.

## Structure des fichiers

```
ezfilters/
├── autoload.php                          # Chargeur automatique et initialisation
├── class-ezfilters-refactored.php        # Classe principale orchestratrice
├── includes/
│   ├── class-ezfilters-config.php        # Configuration et champs ACF
│   ├── class-ezfilters-query.php         # Gestion des requêtes et recherche
│   ├── class-ezfilters-url.php           # URLs et règles de réécriture
│   ├── class-ezfilters-renderer.php      # Rendu des templates
│   ├── class-ezfilters-ajax.php          # Fonctionnalités AJAX
│   ├── class-ezfilters-wpml.php          # Intégration WPML
│   └── class-ezfilters-utils.php         # Utilitaires
└── README-REFACTORED.md                  # Cette documentation
```

## Architecture modulaire

### 1. EZFiltersConfig
**Responsabilités :**
- Configuration des champs ACF
- Gestion des pages d'options
- Chargement et mise en cache des données de configuration
- Configuration des taxonomies et post types

### 2. EZFiltersQuery
**Responsabilités :**
- Traitement des recherches courantes
- Construction des requêtes WordPress
- Gestion des variables de requête
- Optimisation des requêtes de termes

### 3. EZFiltersUrl
**Responsabilités :**
- Génération des URLs filtrées
- Règles de réécriture WordPress
- Gestion des URLs multilingues (WPML)

### 4. EZFiltersRenderer
**Responsabilités :**
- Rendu des templates de filtres
- Rendu des listes de résultats
- Gestion de la pagination
- Logique de fallback pour les templates

### 5. EZFiltersAjax
**Responsabilités :**
- Traitement des requêtes AJAX
- Construction des réponses JSON
- Gestion des hreflangs pour WPML

### 6. EZFiltersWPML
**Responsabilités :**
- Intégration avec WPML
- Traduction des URLs
- Gestion des liens multilingues
- Traduction des termes de taxonomie

### 7. EZFiltersUtils
**Responsabilités :**
- Fonctions utilitaires (slug, validation)
- Sanitisation des données
- Cache des transformations

## Utilisation

### Initialisation automatique
```php
// Dans functions.php ou un plugin
require_once get_template_directory() . '/ezfilters/autoload.php';

// L'instance est automatiquement créée et accessible via :
$filters = ezfilters();
```

### API publique
```php
// Rendu complet
ezfilters()->render('mon_post_type');

// Rendu des filtres uniquement
ezfilters()->renderFilters('mon_post_type');

// Rendu de la liste uniquement
ezfilters()->renderList('mon_post_type');

// Obtenir la recherche courante
$current_search = ezfilters()->getCurrentSearch();

// Générer une URL filtrée
$url = ezfilters()->getFilteredArchiveLink('mon_post_type', $datas);

// Vérifier si la recherche est vide
if (ezfilters()->isCurrentSearchEmpty()) {
    // Aucun filtre actif
}
```

## Avantages de la refactorisation

### 1. **Séparation des responsabilités**
- Chaque classe a une responsabilité unique et bien définie
- Plus facile à tester et déboguer
- Modifications isolées sans impact sur les autres modules

### 2. **Amélioration des performances**
- Cache optimisé pour les termes et entités
- Requêtes groupées pour réduire les appels à la base de données
- Chargement conditionnel des modules WPML

### 3. **Maintenabilité**
- Code plus lisible et organisé
- Easier à étendre avec de nouvelles fonctionnalités
- Documentation intégrée

### 4. **Testabilité**
- Classes isolées faciles à tester unitairement
- Injection de dépendances pour les mocks
- Logique métier séparée de WordPress

### 5. **Extensibilité**
- Architecture modulaire permettant l'ajout de nouveaux modules
- Hooks et filtres pour personnalisation
- API publique stable

## Migration depuis l'ancienne version

L'API publique reste compatible. Le code existant utilisant la classe `EZFilters` continuera de fonctionner sans modification.
```php
// Ancien code - fonctionne toujours
$filters = new EZFilters();
$filters->render();

// Nouveau code recommandé
ezfilters()->render();
```

## Optimisations implémentées

### 1. **Cache des termes**
- Les termes de taxonomie sont chargés par lot
- Cache en mémoire pour éviter les requêtes répétées

### 2. **Optimisation des requêtes**
- Requête unique pour charger tous les termes nécessaires
- Traitement groupé des entités

### 3. **Chargement conditionnel**
- WPML chargé seulement si activé
- Templates chargés à la demande

### 4. **Sanitisation améliorée**
- Validation centralisée des données
- Protection contre les injections

## Développement futur

Cette architecture modulaire facilite l'ajout de nouvelles fonctionnalités :
- Nouveaux types de filtres
- Intégration avec d'autres plugins multilingues
- Cache Redis/Memcached
- API REST pour les filtres
- Interface d'administration améliorée

## Support et maintenance

Le code refactorisé maintient la compatibilité avec l'API existante tout en offrant une base solide pour les évolutions futures. Chaque module peut être développé et testé indépendamment. 