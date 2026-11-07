# Éléments communs

Le dossier `common` contient les grandes zones structurelles et globales du
thème, par exemple le header ou le footer.

Un élément `common` n'est pas une petite brique d'interface autonome et ce
n'est pas une section éditoriale ACF.

| Type | Usage | Appel |
| --- | --- | --- |
| `common` | Zone globale du thème : navigation, footer, bandeau global | `common('nom')` |
| `component` | Brique d'interface réutilisable dans plusieurs contextes | `component('nom', [...])` |
| `strate` | Section de page, généralement alimentée par ACF | `strate('nom', [...])` |

## Structure

Pour un élément nommé `example`, conserver un template PHP plat et les assets
dans leur dossier dédié :

```text
template-parts/common/example.php
assets/common/example/README.md
assets/common/example/example.json      # manifeste Forge
assets/common/example/example.scss
assets/common/example/example.js        # seulement si nécessaire
```

Le README racine décrit la mécanique générale. Le README de chaque élément
doit documenter les données WordPress qu'il utilise, ses règles et ses
dépendances.

Le manifeste déclare le common et toutes ses dépendances statiques. Forge
l'utilise pour ajouter automatiquement les composants, cards, strates, heroes,
autres common et modules nécessaires au site :

```json
{
  "schemaVersion": 1,
  "type": "common",
  "name": "example",
  "slug": "example",
  "dependencies": {
    "components": [],
    "cards": [],
    "strates": [],
    "heroes": [],
    "common": [],
    "modules": []
  }
}
```

Tout appel statique fait par le common doit être reflété dans ce manifeste.
Les emplacements utilisés par `wp_nav_menu()` sont déclarés dans
`dependencies.menus`, avec leurs identifiants exacts, par exemple
`"menus": ["menu-header"]`. Chaque emplacement doit posséder un manifeste
dans `assets/menus`. Forge ajoute ces menus automatiquement, sans analyser
ni exécuter le PHP.

## Variantes dans Forge

Un common peut proposer plusieurs versions sous une seule entrée du catalogue.
Le manifeste de chaque variante déclare le nom canonique qu'elle remplace :

```json
"variantOf": "header-nav"
```

Par exemple, `assets/common/header-nav-subnav/header-nav-subnav.json`
décrit une variante de `header-nav`. Elle garde son propre PHP, ses assets,
son README et ses dépendances. Forge affiche cette version dans un select,
et les dépendances du site suivent la version sélectionnée.

Au build, Forge copie la variante choisie vers le nom canonique et renomme ses
fichiers, identifiants CSS, chemins JS et attributs `data-module`. Le manifeste
généré reprend le slug canonique, ses règles d'activation et les dépendances
de la variante ; `variantOf` est retiré. Les versions non utilisées sont
supprimées du site créé. Les pages continuent à appeler le nom canonique.

Ce contrat s'applique à tous les common, notamment header, footer et prefooter.
La variante doit désigner un common de base existant, sans chaîne de variantes,
et ne pas dépendre du common qu'elle remplace. Ses données WordPress doivent
respecter le contrat du rôle choisi (menus, options ACF) ; ces identifiants
métier restent explicites dans son PHP et doivent exister dans le projet.

## Appel PHP

```php
<?php common('example'); ?>
```

Un common ne reçoit aucun tableau d'arguments. Il connaît sa responsabilité
globale et récupère directement les données correspondantes dans WordPress :

```php
<?php
$location = 'menu-example';

if (has_nav_menu($location)) {
    wp_nav_menu([
        'theme_location' => $location,
        'container' => false,
    ]);
}
?>
```

Il peut utiliser les menus WordPress, les options du site ou une page d'options
ACF. Il ne doit utiliser ni `$params`, ni `$args`, ni `get_template_args()`.

## Options ACF

Le menu parent `Options` regroupe les réglages des éléments globaux. Chaque
common possédant des données administrables utilise sa propre sous-page :

```text
Options
├── Prefooter
└── Header       # à créer lorsque le header aura ses propres options
```

La sous-page et la règle de localisation du groupe ACF utilisent un slug
explicite et stable, par exemple `theme-options-prefooter`. Les valeurs restent
enregistrées avec le contexte ACF global `option`, donc le common les lit avec :

```php
get_field('prefooter', 'option');
```

Un nom vide ou contenant `/` ou `..` est refusé. Passer uniquement le nom du
dossier, sans chemin et sans extension :

```php
common('header-nav'); // correct
common('common/header-nav'); // incorrect
common('header-nav.php'); // incorrect
```

## CSS

Le SCSS reste co-localisé dans :

```text
assets/common/example/example.scss
```

Un élément global présent sur toutes les pages est importé sans option dans
`assets/app.scss` :

```scss
@import "common/example/example";
```

Un élément common réellement optionnel peut être déclaré avec `split`. Le build
produit alors `assets/css/common/example/example.css` et `common('example')`
l'enregistre à la demande :

```scss
@import "common/example/example" split;
```

Ne pas cumuler les deux formes pour le même élément.

## JavaScript

`common()` enregistre le CSS, mais ne lance pas à lui seul le JavaScript. Deux
fonctionnements sont possibles.

### Élément global toujours présent

Pour un comportement global initialisé une seule fois, importer directement le
module dans `assets/app.js` :

```js
import initExample from "@/common/example/example";

initExample();
```

Cette solution convient à un élément structurel présent sur presque toutes les
pages et dont l'initialisation ne dépend pas d'un élément racine hydraté.

### Élément hydraté à la demande

Pour un élément optionnel ou coûteux, déclarer le module dans son HTML :

```php
<div class="example" data-module="common/example" data-context="@visible true">
    <!-- contenu -->
</div>
```

Puis exporter une fonction recevant cet élément racine :

```js
export default function initExample(root) {
    const button = root.querySelector("button");

    if (!button) return;

    const onClick = () => {};
    button.addEventListener("click", onClick);

    return () => button.removeEventListener("click", onClick);
}
```

La fonction retournée sert au nettoyage si l'élément est retiré du DOM. Ne pas
cumuler l'import direct dans `app.js` et `data-module` pour le même module, sous
peine de lancer son initialisation deux fois.

## Règles

- Utiliser `common()` pour afficher l'élément ; ne pas appeler directement son
  `get_template_part()` depuis les templates de page.
- Appeler le common uniquement avec son nom, sans payload.
- Récupérer ses données globales depuis WordPress dans son template : menus,
  options du site ou options ACF.
- Échapper les valeurs au moment de leur sortie selon leur contexte HTML.
- Réutiliser les composants avec `component('nom', [...])` au lieu de dupliquer
  leur balisage.
- Ne pas placer dans `common` un composant utilisable isolément ou une strate
  administrable.
- Ajouter un README dans le dossier d’assets de chaque nouvel élément.

## Éléments disponibles

| Nom | Appel | Documentation |
| --- | --- | --- |
| `breadcrumb` | `common('breadcrumb')` | [`breadcrumb/README.md`](breadcrumb/README.md) |
| `header-nav` | `common('header-nav')` | [`header-nav/README.md`](header-nav/README.md) |
| `related` | `common('related')` | [`related/README.md`](related/README.md) |
| `prefooter` | `common('prefooter')` | [`prefooter/README.md`](prefooter/README.md) |
| `prefooter-newsletter` | `common('prefooter-newsletter')` | [`prefooter-newsletter/README.md`](prefooter-newsletter/README.md) |
| `footer` | `common('footer')` | [`footer/README.md`](footer/README.md) |
