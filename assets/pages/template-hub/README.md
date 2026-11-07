# Hub

Page qui affiche la liste paginée d’un Content Type.

À utiliser comme page d’entrée d’une rubrique, ou comme rendu générique d’une archive. Le Content Type affiché est configuré pour la page.

## Pagination

`Theme_Listing` utilise `pretty_urls => true`. Sans filtres actifs, les liens
sont de la forme `/news/page/2/` (à partir de l’URL de la page Hub).
Le paramètre `hub_page` reste accepté pour les anciennes URLs.
Les permaliens WordPress doivent être activés.

La pagination utilise les règles natives des pages WordPress. Si le CPT et
la page Hub partagent le même slug, le filtre `request` donne la priorité à
la page Hub pour son chemin `/page/N/`, sans modifier les archives natives.

## AJAX

`ajax => true` active la pagination sans rechargement via le coordinateur
`components/filters`, même sans formulaire de filtres. La région `.hub-content`
porte les attributs produits par `$listing->results_attributes()`. Elle contient
la liste et la pagination ; seule sa contribution est remplacée. L’URL et
l’historique suivent la navigation. Sans JavaScript, les liens restent utilisables.
Avec `ajax => false`, la même région ne reçoit aucun module JavaScript.

Le nom du paramètre de pagination se configure avec `pagination_slug`
(`'pagination_slug' => 'page'` dans ce template), anciennement `page_name`.
Cette option ne change pas le segment `/page/` du routage des URLs propres.
