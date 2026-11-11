# Header avec CTA

```php
common('header-nav-cta');
```

## Structure et données

Logo à gauche, menu principal centré et bouton d'action à droite sur desktop.
Le bouton fait partie du panneau sur mobile.

- `menu-header` : navigation principale, premier niveau uniquement.
- `menu-header-cta` : le premier lien de premier niveau fournit le CTA.
  Son titre, son URL et sa cible sont transmis à `component('btn')`.
  Les autres entrées et les enfants de ce menu ne sont pas utilisés.
- Sans lien d'action valide, aucun bouton n'est produit.
- Pas de recherche dans cette variante.

Le nom et l'URL du site viennent de WordPress, le logo du symbole `logo`.
Le common ne reçoit aucun argument et ne lit aucun champ ACF.
Les menus sont rendus avec le walker nettoyant les classes et IDs WordPress.

## Interaction

Le bouton ouvre le panneau avec `aria-expanded`, place le focus sur son premier
élément interactif et garde Tab/Maj+Tab dans la navigation. Échap ferme le panneau
et rend le focus au bouton. Les liens ferment aussi le panneau.
La navigation fermée est `inert`. Un panneau vide reçoit lui-même le focus.
Les animations respectent `prefers-reduced-motion` et le scroll interne utilise
`data-lenis-prevent`. Le header reste fixe, comme le header classique.

## Forge et assets

Le manifeste déclare `variantOf: "header-nav"`. Forge affiche cette version dans
le sélecteur du header et ajoute ses menus et composants. À la génération, ses
fichiers et sélecteurs sont renommés en `header-nav`. Un seul header par page.

SCSS critique et JS global dans `assets/app.scss` et `assets/app.js`. Aucun
`data-module` supplémentaire : ne pas initialiser deux fois la navigation.
Les fichiers sont indépendants des autres variantes pour rester adaptables
une fois copiés dans un site. Aucun moteur de configuration n'est nécessaire.

## Vérifications

```sh
php web/wp-content/themes/starterkit-lonsdale-2027/tests/header-variants-contract.php
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-mobile-focus-contract.mjs
```
