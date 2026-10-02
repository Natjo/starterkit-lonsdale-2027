# Header avec sous-menus

Variante de `header-nav`, déclarée avec `variantOf` dans son manifeste.
Forge la propose dans le select Version du header et la renomme en `header-nav`
au build. Les pages conservent leurs appels à `common('header-nav')`.
Le manifeste déclare `dependencies.menus: ["menu-header"]` ; ce menu est
ajouté automatiquement par Forge et affecté à son emplacement au build.

Elle utilise le même menu WordPress `menu-header`, le logo et la recherche que
le header standard. La hiérarchie du menu détermine les sous-menus ; chaque
parent conserve son lien. Sur ordinateur, le sous-menu s'ouvre au survol
ou lorsque Tab place le focus dans son parent, sans bouton supplémentaire.
Sur mobile ou sur un appareil sans survol, un bouton distinct ouvre les enfants
sans détourner le lien parent.
Chaque `ul.sub-menu` est enveloppé dans un `div.level1`, comme dans
le header standard, à tous les niveaux enfants. Le `ul` principal reste sans
wrapper. Le JavaScript place le bouton d'ouverture avant ce wrapper.
Le walker retire les classes techniques des `li` (`menu-item`,
`menu-item-type-*`, `menu-item-object-*`, `menu-item-{ID}` et
`menu-item-has-children`), mais conserve les états actifs et les classes personnalisées.
Le JavaScript repère les parents par leur enfant direct `.level1`.
La classe `is-submenu-open` du parent affiche ce wrapper ; les sous-menus
sont masqués au repos. Ouvrir un sous-menu ferme ses frères, au clic comme
au survol. Sur mobile, les wrappers reprennent leur place dans le flux.

Sur ordinateur, `--submenu-height` vaut `0px` sur chaque wrapper fermé.
Le CSS lui attribue `6rem` sur `.is-submenu-open > .level1`,
puis `0px` à la fermeture. Wrapper et `ul` utilisent `height: var(--submenu-height)`
et la même transition de `250ms`. Le contenu est aligné en bas, avec `overflow: hidden`
sur le `ul` uniquement pendant le dévoilement. Une fois ouvert,
`is-submenu-revealed` rétablit `overflow: visible` pour les niveaux imbriqués,
pour créer un effet de dévoilement sans transform ni fondu. La hauteur correspond
aux éléments de sous-menu de `6rem` : modifier ensemble ces deux valeurs si
le design change. Aucun calcul de hauteur ni observer n'est nécessaire. Au repos,
`inert` et `pointer-events: none` excluent les sous-menus du parcours clavier
et des interactions. Sur mobile, les wrappers fermés restent
en `display: none` pour ne pas laisser d'espace dans le panneau. Les
transitions sont désactivées avec `prefers-reduced-motion: reduce`.
Wrapper et sous-menu se touchent sans espace pour garder un survol continu.
Sur ordinateur, le wrapper prend la largeur de son parent. Les sous-menus de
premier niveau restent alignés à gauche, sauf si leur bord droit dépasserait
la navigation : le JS ajoute alors `.right` au wrapper pour les ferrer à droite.
Le calcul est refait à l'ouverture, après chargement des polices et au
redimensionnement (regroupé dans une RAF). La classe est retirée lorsque
l'espace redevient suffisant ou sur mobile.
Un pseudo-élément `::after` superpose la bordure basse du header aux fonds
des sous-menus, sans intercepter les interactions ni créer d'espace au survol.
Le focus du bouton tactile n'ouvre pas le menu avant son clic : un clic produit
une seule bascule.
L'initialisation du header est idempotente pour éviter les boutons en double.
Les `id` des `li` et du `ul` principal sont supprimés. Les identifiants des sous-menus utilisés
par `aria-controls` sont conservés.
Échap referme un sous-menu et replace le focus sur le lien parent sur
ordinateur, ou sur le bouton d'ouverture sur mobile / sans survol.

Sur mobile, les sous-menus s'ouvrent dans le panneau de navigation, sans
positionnement absolu. Les réglages de hauteur et de compaction du header
standard s'appliquent aussi à cette variante.

`Navigation` gère uniquement le panneau mobile : burger, fermeture par lien
ou Échap, retour du focus sur le burger avec Échap et `data-lenis-prevent`.
À l'ouverture, le premier élément interactif visible reçoit le focus.
Le déplacement attend deux frames de rendu pour la transition de visibilité,
au clic comme avec Entrée, et est annulé si le panneau est refermé.
Tab et Maj+Tab restent dans `#nav` et excluent les éléments masqués,
désactivés ou `inert`. Si le menu est vide, `#nav` reçoit le focus.
Échap ferme tout le panneau, y compris depuis un sous-menu, et rend le
focus au burger. La navigation mobile fermée est `inert` ; aucun piège
de focus n'est actif sur desktop.
Test : `tests/header-nav-mobile-focus-contract.mjs` (depuis le dossier du thème).
Classes et attributs sont modifiés seulement si leur valeur change. Le timer
de `300ms` retire `is-nav-animating` après une bascule ; l'initialisation et
les changements de breakpoint réinitialisent le panneau sans transition.

Sans JavaScript, les sous-menus restent accessibles au survol et au focus sur
ordinateur ; leurs liens sont visibles dans la navigation mobile.

Pour prévisualiser cette variante dans le starter, utiliser
`common('header-nav-subnav')`. `assets/app.js` initialise la version standard
ou complexe selon la racine présente. Un seul header doit être rendu par page.

## Vérification navigateur

`HeaderScroll` reste local à cette variante. Les classes de compaction et de
masquage sont modifiées uniquement si leur état change. Le sens du scroll
n'est suivi que si `hideOnScroll` est actif ; aucun listener `scroll` n'est
installé lorsque `hideOnScroll` et `compactOnScroll` sont désactivés. Le
listener actif reste passif et ses mises à jour sont regroupées dans une RAF.

Après compilation des assets, exécuter depuis la racine du starter :

```sh
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-subnav-scroll-contract.mjs
node web/wp-content/themes/starterkit-lonsdale-2027/tests/header-nav-subnav-browser-contract.mjs
```

Le test utilise Chrome headless et le protocole DevTools, sans dépendance npm.
Sur macOS, Chrome est détecté à son emplacement habituel ; ailleurs, définir
`CHROME_BIN` avec le chemin de l'exécutable. Il contrôle le focus clavier,
Échap, l'animation et son clipping temporaire, les niveaux imbriqués, le
bouton tactile, le mobile, la réduction des animations et l'idempotence.
Il crée un profil temporaire et le supprime après fermeture du navigateur.

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
