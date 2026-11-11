# header-nav-mega

Variante de `header-nav`, disponible dans Forge via `variantOf`.
Au build, la variante sélectionnée est renommée en `header-nav`.

## Utilisation

```php
common('header-nav-mega');
```

Un seul header par page, sans arguments. La home du starter utilise cette
variante. Le CSS critique et le JS global sont importés dans `assets/app.scss`
et `assets/app.js`. Le JS cible `#header-nav-mega` et les sous-menus du
walker spécifique `Theme_Mega_Nav_Walker` dans `.level1` ;
le menu principal est `.nav-menu`. Les autres headers restent inchangés.

## Données et dépendances

Menu WordPress `menu-header`, nom et URL du site, composants `icon` et
`search`. La recherche est omise si `THEME_ENABLE_SEARCH` est désactivée.
Le manifeste déclare ces composants et le menu, ajouté automatiquement
par Forge. Les classes techniques et IDs WordPress des items sont retirés ;
chaque wrapper principal reçoit un ID unique utilisé par `aria-controls`.
Les listes `.sub-menu` ne reçoivent pas d'ID.

## Hiérarchie du menu

Le même `wp_nav_menu()` lit la hiérarchie configurée dans WordPress.
Chaque entrée principale possède un panneau `.level1` contenant
ses sous-pages. Chaque sous-page liste ses sous-sous-pages dans son propre
`ul.sub-menu`, sans wrapper ni bouton supplémentaire. Les niveaux plus
profonds restent imbriqués sous leur parent.
Sur desktop, les sous-pages sont des colonnes avec leurs descendants.
La hauteur naturelle du panneau complet est calculée en JS dans `--height`
sur le wrapper ; elle est actualisée au redimensionnement et lors des
changements de contenu. Sur mobile, la hauteur reste automatique.
Sur desktop, `--min-height: 10rem` sur le header harmonise les panneaux.
Ce minimum est appliqué à la liste principale à l'intérieur du panneau :
sa mesure JS l'inclut dans `--height`. Le contenu peut être plus haut.
Le panneau lui-même anime seulement `height` et se ferme complètement
à zéro, sans saut causé par une hauteur minimale sur le conteneur animé.

## Comportement

Sous-menus au survol et au focus sur desktop ; boutons d'ouverture sur
mobile et écrans tactiles. Échap, fermeture des menus frères et descendants,
clic extérieur et changement de breakpoint sont gérés par le JS.
Le panneau mobile conserve la gestion du focus et de Tab/Maj+Tab.
Le header est compacté après 100 px de scroll, sans masquage directionnel.
Les animations respectent `prefers-reduced-motion`.

## Vérification

```sh
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-mega-browser-contract.mjs
```

Contrôle un troisième niveau, les IDs sur les wrappers, `aria-controls`,
les hauteurs et les interactions clavier/mobile.

## Structure du panneau

```html
<div class="level1" id="…">
    <div class="wrapper-sub-menu">
        <ul class="sub-menu">…</ul>
    </div>
</div>
```

Le panneau contrôlé est `level1` (ID, `aria-controls`, `inert`, état ouvert).
La div intérieure `wrapper-sub-menu` entoure la liste.


## Réinitialisation d'un aperçu

L'initialiseur accepte une racine optionnelle : `const destroy = HeaderNav(root)`.
Sans argument, il retrouve le header par son ID comme auparavant. Un deuxième appel
sur la même racine retourne le même nettoyage, sans doubler les interactions.
`destroy()` retire les écouteurs, pièges de focus, timers, RAF et observers créés
par cette instance. L'événement local `theme:header-destroy` déclenche aussi ce
nettoyage avant le remplacement d'une racine initialisée globalement.

Dans le styleguide, seule la racine ajoutée en AJAX reçoit `data-module` et passe
ensuite sous le contrôle de l'hydrateur. Le premier header conserve l'import global
habituel ; ne pas lui ajouter simultanément `data-module`.

Les boutons de sous-menu affichent un caret qui suit `aria-expanded`,
sur desktop et mobile, au lieu des symboles plus/moins.
