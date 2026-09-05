# Common `header-nav`

Header global du thème. Il affiche le logo, la recherche du site et, lorsqu’un
menu est affecté à l’emplacement WordPress `menu-header`, sa navigation.

## Appel

```php
<?php common('header-nav'); ?>
```

Le common ne reçoit aucun argument. Il récupère directement ses données
globales dans WordPress.

## Rendu

Le template produit toujours :

- `#header-nav`, racine utilisée par le CSS et le JavaScript ;
- un lien vers l’accueil ;
- le composant `icon` avec le symbole `logo` ;
- le nom du site comme libellé accessible ;
- `.btn-nav`, placé avant la navigation pour conserver l’ordre clavier mobile ;
- `#nav`, relié au bouton par `aria-controls` ;
- le composant `search`, envoyé en GET vers l’accueil.

Si `menu-header` possède un menu, celui-ci est affiché avant la recherche dans
`#nav`. Sans menu affecté, la recherche reste accessible et le bouton burger
ouvre toujours son panneau sur mobile.

## Données WordPress

| Donnée | Source |
| --- | --- |
| Menu principal | emplacement `menu-header` |
| Nom du site | `get_bloginfo('name')` |
| URL d’accueil | `home_url('/')` |
| Recherche courante | `get_search_query()` |
| Libellés accessibles | domaine de traduction `theme` |

Le menu est rendu avec `wp_nav_menu()`, sans conteneur supplémentaire et sans
fallback. Le formulaire utilise `component('search', [...])`.

## Initialisation JavaScript

Le header est présent sur toutes les pages. Son JavaScript est donc importé
directement dans `assets/app.js` :

```js
import HeaderNav from '@common/header-nav/header-nav';

HeaderNav();
```

Le template ne doit pas recevoir `data-module="common/header-nav"`. Cumuler
l’import global et l’hydratation initialiserait le header deux fois.

`header-nav.js` sépare trois responsabilités :

- `HeaderNav()` trouve `#header-nav` et initialise le comportement ;
- `Navigation()` gère le burger et le menu responsive ;
- `HeaderScroll()` gère la compaction et le masquage directionnel.

L’initialisation reste active pendant toute la durée de la page. Elle ne
retourne pas de lifecycle destiné à l’hydrateur.

## Navigation mobile

Le breakpoint JavaScript correspond au breakpoint CSS mobile :

```js
const MOBILE_MEDIA = '(max-width: 767px)';
```

Le bouton :

- synchronise `aria-expanded` avec `is-open` ;
- se transforme en croix lorsque le menu est ouvert ;
- ferme le menu après activation d’un lien ;
- ferme le menu avec `Escape` et récupère le focus ;
- ferme et réinitialise son état au passage en desktop.

Pendant l’ouverture, `#nav` reçoit temporairement `data-lenis-prevent` afin que
son scroll interne ne soit pas capturé par Lenis. Le CSS ajoute également
`overscroll-behavior: contain`. Sa hauteur est limitée à celle du viewport et
ses contenus ne se contractent pas : lorsque le menu dépasse l’espace
disponible, `#nav` défile verticalement.

### Transitions sans flash

Le panneau mobile est masqué par défaut et ne possède aucune transition au
chargement ou pendant un changement de breakpoint.

`is-nav-animating` active la transition uniquement pendant les 300 ms qui
suivent une ouverture ou une fermeture volontaire. Cela évite l’apparition
brève du menu au chargement et lors du passage desktop/mobile.

## Scroll du header

`HeaderScroll()` accepte les options suivantes :

| Option | Défaut | Effet |
| --- | ---: | --- |
| `compactOnScroll` | `false` | Ajoute `is-compact` après le seuil. |
| `hideOnScroll` | `false` | Ajoute `is-hidden` en descendant après le seuil. |
| `threshold` | `0` | Seuil commun exprimé en pixels. |

Configuration actuelle :

```js
HeaderScroll(headerNav, {
    hideOnScroll: false,
    compactOnScroll: true,
    threshold: 100,
});
```

Le header devient donc compact après `100px`, mais il ne se masque pas
actuellement pendant un scroll descendant. Passer `hideOnScroll` à `true`
active ce second comportement.

La lecture de `window.scrollY` utilise un listener passif et est limitée à une
mise à jour par frame avec `requestAnimationFrame`.

### Rechargement à une position restaurée

Si la page est rechargée après avoir scrollé, `is-compact` est appliquée
immédiatement sans transition.

Lorsque la page est prête — immédiatement si elle est déjà chargée, sinon après
`pageshow` — deux frames sont attendues avant que `is-scroll-ready` active les
futures transitions de hauteur. Le header ne passe donc pas visuellement de sa
hauteur normale à sa hauteur compacte pendant le chargement.

### Focus clavier

Un header masqué redevient visible dès que le focus entre dans l’un de ses
éléments. Tant que le focus reste dans le header, le scroll ne lui ajoute pas
`is-hidden`.

## Classes d’état

| Classe | Rôle |
| --- | --- |
| `is-open` | Menu mobile ouvert. |
| `is-nav-animating` | Transition temporaire du panneau mobile. |
| `is-compact` | État compact du header après le seuil. |
| `is-hidden` | Header déplacé hors écran pendant un scroll descendant. |
| `is-scroll-ready` | Transitions de hauteur autorisées après le rendu initial. |
| `has-compact-header` | État miroir sur `<html>` qui synchronise la hauteur globale. |

## CSS

Le SCSS utilise `#header-nav` comme racine. La hauteur normale vient de
`--header-height-expanded` et la hauteur compacte de
`--header-height-compact`. `HeaderScroll()` publie l’état compact sur `<html>`
afin que `--header-height` reflète la hauteur réelle dans tout le document :

```scss
:root {
    --header-height-expanded: 9rem;
    --header-height-compact: 7rem;
    --header-height: var(--header-height-expanded);
}

:root.has-compact-header {
    --header-height: var(--header-height-compact);
}
```

Le panneau mobile utilise la même variable dans son padding supérieur. Modifier
la hauteur compacte conserve donc l’alignement entre le header et le contenu du
menu.

La recherche possède une largeur limitée sur desktop et occupe toute la
largeur disponible dans le panneau mobile.

Le fond du panneau peut être personnalisé avec :

```scss
#header-nav {
    --header-nav-menu-background: #fff;
}
```

Le SCSS du header et celui du composant `search` sont importés comme CSS
critique dans `assets/app.scss`, car ils sont présents sur toutes les pages.
Ils ne doivent pas être ajoutés une seconde fois dans une autre entrée SCSS.

Les transitions du header et du panneau mobile sont désactivées avec
`prefers-reduced-motion: reduce`.

## Accessibilité

- le header utilise le rôle `banner` ;
- le logo possède un nom accessible ;
- le bouton conserve le libellé masqué « Menu » ;
- ses trois traits sont décoratifs ;
- `aria-expanded` et `aria-controls` décrivent l’état du menu ;
- la navigation possède le libellé « Navigation principale » ;
- le bouton précède le menu dans le DOM ;
- `Escape` ferme le menu et replace le focus.
- le formulaire possède le rôle `search` et un champ correctement libellé.

## Vérifications

Le contrat navigateur
`tests/header-nav-browser-contract.html` contrôle notamment :

- l’ordre du bouton et du menu ;
- l’ouverture, la fermeture et `aria-expanded` ;
- la fermeture par lien, avec `Escape` et au passage desktop ;
- l’activation temporaire des transitions et de `data-lenis-prevent` ;
- le démarrage compact sans transition après restauration du scroll ;
- la réapparition au focus.

Le résultat attendu est :

```html
<body data-result="ok">
```

Après une modification du template ou des assets, les pages déjà exportées par
WP Static doivent être régénérées.

## Fichiers

```text
template-parts/common/header-nav.php
assets/common/header-nav/README.md
assets/common/header-nav/header-nav.scss
assets/common/header-nav/header-nav.js
assets/app.js
tests/header-nav-browser-contract.html
```
