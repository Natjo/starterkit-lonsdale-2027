# Hub filters

Modèle sélectionnable `template-hub-filters.php` : liste générique de Content Type
avec recherche, filtres de taxonomies, tri et pagination.

## Configuration

Le Content Type est choisi dans Forge puis stocké dans la méta WordPress
`hub_post_type`. Cette méta doit être renseignée (par exemple `project` ou
`expertise`) : aucun CPT n’est fixé par défaut. La card est résolue depuis la
configuration du CPT. Les taxonomies publiques associées alimentent les filtres,
combinés avec une relation `AND`.

Le hero `page` puis les strates ACF précèdent la liste. Sur une archive native,
le modèle utilise le CPT courant et le titre de l’archive, sans strates de page.

## Présentation

La recherche, les filtres et le tri sont regroupés au-dessus des résultats,
dans `.main-content > .filters`. La liste utilise `.results-list`.
La région `.results` porte l’identifiant retourné par `$listing->target()`.

Le CSS `pages/template-hub-filters/template-hub-filters` est importé en `once` dans
`assets/app.scss`. Il est compilé séparément puis injecté inline lorsque
WordPress exécute ce template ; il n’est pas intégré au fichier `app.css`.

## Navigation

La recherche, les filtres, le tri et la pagination utilisent AJAX lorsque
JavaScript est disponible. Les formulaires GET et les liens de pagination
restent utilisables sans JavaScript. Le tri par défaut affiche les contenus
les plus récents ; les autres choix sont date croissante et titre A–Z ou Z–A.

Les URLs propres utilisent les segments `/filters/` et `/page/N/`.
Le paramètre de pagination configuré dans `Theme_Listing` est `hub_page`.
Les permaliens WordPress doivent être activés.

## Différence avec les autres Hubs

`template-hub.php` affiche uniquement la liste paginée. Les deux modèles
Hub filters partagent la même configuration de recherche et de filtres ; leur
différence porte sur la disposition des contrôles.
