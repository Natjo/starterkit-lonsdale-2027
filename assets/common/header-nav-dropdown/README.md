# Header Dropdown

Variante déroulante de `header-nav`, proposée par Forge avec `variantOf`.
Au build Forge, la variante choisie est renommée en `header-nav`.

## Utilisation et assets

```php
common('header-nav-dropdown');
```

Un seul header par page. Ce common ne reçoit aucun argument.
Le CSS critique et le JS global sont importés dans `assets/app.scss` et
`assets/app.js`. Le JS cible `#header-nav-dropdown`.

## Données et dépendances

Le template lit le menu WordPress `menu-header`, le logo et la recherche
comme le header standard. Le manifeste déclare les composants `icon`,
`search` et le menu `menu-header`, ajouté automatiquement par Forge.
Le walker partagé enveloppe les sous-menus dans `.level1` et
retire les classes techniques et IDs WordPress des éléments de menu.
Les classes personnalisées et les états actifs sont conservés. Le `ul`
principal utilise `.nav-menu` sans ID ; les panneaux `.level1` reçoivent
les IDs nécessaires aux boutons `aria-controls`.
La recherche est omise lorsque `THEME_ENABLE_SEARCH` est désactivée.
Le common ne lit pas de champs ACF et ne dépend d'aucun module ou vendor.

## Interactions

Sur desktop (à partir de 768 px), les sous-menus s'ouvrent au survol ou au
focus sur leur lien parent. Sur mobile et écran tactile, un bouton séparé
permet de les ouvrir sans détourner le lien du parent.
Ouvrir un sous-menu ferme ses frères ; fermer un parent ferme ses enfants.
Échap referme le sous-menu et replace le focus sur son lien parent ou sur
son bouton tactile. Un clic extérieur et un changement de breakpoint
referment les sous-menus. Les wrappers fermés sont `inert`.
Le positionnement des dropdowns est défini uniquement par le CSS, sans
correction automatique de leur alignement en JS.
Les animations respectent `prefers-reduced-motion` uniquement via le CSS.
Le dropdown utilise seulement `is-submenu-open` : aucun état de dévoilement
ni listener de fin de transition. Le wrapper conserve son clipping.

Le burger ouvre le panneau mobile avec `aria-expanded` et
`data-lenis-prevent`. Un lien ou Échap le referme. La classe
`is-nav-animating` limite la transition à une bascule volontaire.
À l'ouverture mobile, le focus va au premier élément interactif visible.
Le déplacement attend deux frames de rendu pour la transition de visibilité,
au clic comme avec Entrée, et est annulé si le panneau est refermé.
Tab et Maj+Tab bouclent dans `#nav`, sans éléments masqués, désactivés ou
`inert`. Un menu vide reçoit lui-même le focus. Échap ferme tout le panneau,
même depuis un sous-menu, et rend le focus à `.btn-nav`. Les sous-menus
sont refermés avec le panneau ; la navigation mobile fermée est `inert`.
Le piège de focus est retiré à la fermeture et n'est pas actif sur desktop.
`HeaderScroll` compacte le header après 100 px ; le masquage au scroll
est désactivé dans la configuration actuelle. L'initialisation est idempotente.

## Hauteur des sous-menus

Sur desktop, `.level1` anime `grid-template-rows` de `0fr` à `1fr`.
Son enfant `.wrapper-sub-menu` utilise `min-height: 0` et `overflow: hidden`
pour permettre la fermeture complète. Le contenu définit la hauteur ouverte,
y compris après modification du contenu ou chargement des polices.
Aucune mesure JS, variable `--height` ou `ResizeObserver` n'est nécessaire.
Sur mobile, les panneaux restent dans le flux et sont affichés/masqués sans
animation de hauteur. Le JS conserve les états ouverts et l'accessibilité.

## Vérification

Après compilation :

```sh
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-dropdown-browser-contract.mjs
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-mobile-focus-contract.mjs
```

Vérifie les hauteurs intermédiaires de l'animation d'ouverture/fermeture,
le contenu dynamique, le clavier, Échap, `inert`, le mobile, la fermeture
extérieure et l'initialisation sans boutons en double.

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
