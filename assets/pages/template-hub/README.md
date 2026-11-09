# Hub

Page qui affiche la liste paginée d’un Content Type.

Le modèle `template-hub.php` est sélectionnable dans WordPress et Forge.
Le Content Type est choisi dans Forge et enregistré dans la méta WordPress
`hub_post_type` (par exemple `team`). Cette méta doit être renseignée : le modèle
ne fixe aucun CPT par défaut. La card est résolue depuis la configuration du CPT.

Le hero `page` et les strates ACF précèdent la liste. Ce modèle ne propose
ni recherche, ni filtres, ni tri. Sur une archive native, il utilise le CPT
courant et le titre de l’archive, sans rendre les strates de page.

Le CSS `pages/template-hub/template-hub` est importé en `once` dans
`assets/app.scss`, compilé séparément puis injecté inline pour ce template.

## Pagination

`Theme_Listing` utilise `pretty_urls => true`. Sans filtres actifs, les liens
sont de la forme `/news/page/2/` (à partir de l’URL de la page Hub).
Les permaliens WordPress doivent être activés.

La pagination utilise les règles natives des pages WordPress. Si le CPT et
la page Hub partagent le même slug, le filtre `request` donne la priorité à
la page Hub pour son chemin `/page/N/`, sans modifier les archives natives.

## AJAX

`ajax => true` active la pagination sans rechargement via le coordinateur
`components/filters`, même sans formulaire de filtres. La région `.results`
porte les attributs produits par `$listing->results_attributes()`. Elle contient
la liste et la pagination ; seule sa contribution est remplacée. L’URL et
l’historique suivent la navigation. Sans JavaScript, les liens restent utilisables.
Avec `ajax => false`, la même région ne reçoit aucun module JavaScript.

Le nom du paramètre de pagination se configure avec `pagination_slug`
(`'pagination_slug' => 'page'` dans ce template), anciennement `page_name`.
Cette option ne change pas le segment `/page/` du routage des URLs propres.

## Team

Le CPT Team désactive son archive native. Sa page `/team/` utilise
`template-hub.php` avec la méta `hub_post_type = team`, pour afficher les cards
Team avec la pagination du Hub.
