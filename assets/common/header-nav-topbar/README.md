# Header avec double navigation

```php
common('header-nav-topbar');
```

## Structure et données

Une barre utilitaire précède la ligne principale (logo, liens, recherche optionnelle).
Elle reste visible sur mobile, avec défilement horizontal si nécessaire. Le menu
principal passe dans le panneau. La barre peut recevoir des liens Aide, Espace
client ou Langues ; elle ne fabrique pas de sélecteur de langue ni de traduction.

Menus WordPress : `menu-header` et `menu-header-utility`, premier niveau.
Le common ne reçoit aucun argument. Il utilise le nom du site, son URL et le
symbole `logo`. Aucun champ ACF ou plugin métier n'est requis.
Les classes techniques et IDs des items de menus sont nettoyés par le walker.

## Mobile et clavier

Le bouton Menu ouvre le panneau et synchronise `aria-expanded`. Le focus rejoint
le premier élément visible ; Tab/Maj+Tab restent dans le panneau. Échap ferme le
menu et rend le focus au bouton. Les liens ferment le panneau. La navigation fermée
est `inert` sur mobile, puis redevient disponible sur desktop. Les animations
respectent `prefers-reduced-motion` ; le scroll interne utilise `data-lenis-prevent`.

## Assets et Forge

Un seul header par page. CSS critique et JS global sont importés dans
`assets/app.scss` et `assets/app.js`, sans double initialisation par `data-module`.
Le manifeste déclare ses menus et composants, avec `variantOf: "header-nav"`.
Forge renomme la variante retenue en `header-nav` et ajoute ses dépendances.
Les fichiers de chaque variante sont indépendants pour être adaptés simplement.
Le header reste fixe. Les hauteurs et espacements se règlent dans son SCSS ;
les modèles à deux lignes ajustent `--header-height` sur `:root:has(#...)`.

## Vérifications

```sh
php web/wp-content/themes/starterkit-lonsdale-2027/tests/header-variants-contract.php
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-mobile-focus-contract.mjs
```
