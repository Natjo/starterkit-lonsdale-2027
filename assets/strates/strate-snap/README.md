# Strate `snap`

Strate statique de démonstration composée de quatre sections colorées. Elle les
parcourt comme un slider vertical sur ordinateur, sans Swiper ni `lenis-snap`.
La navigation et le comportement sont délégués au composant `section-snap`.

## Appel

```php
strate('snap');
```

Les arguments transmis sont ignorés. La strate rend toujours quatre panneaux
de démonstration : noir, bleu, gris et blanc. Elle n’est pas déclarée dans ACF.

Le composant configure la ligne d’activation :

```js
sectionSnap(root, {
    slideSelector: ":scope > .strate",
    navSelector: ":scope > .snap-nav",
    activationOffset: "var(--header-height)",
    deactivationOffset: "var(--header-height)",
    breakpoint: 1025,
});
```

`activationOffset` et `deactivationOffset` peuvent être remplacés indépendamment
par une autre longueur CSS, un pourcentage, un `calc()` ou un nombre de pixels.
Les pourcentages sont relatifs à la hauteur du viewport : `"50%"` correspond à
`50vh`. Le premier contrôle l'entrée par le haut du conteneur ; le second
contrôle sa sortie par le bas.

`breakpoint` est une largeur minimale exprimée en pixels. En dessous de `1025px`
dans cette strate, le snap et ses variables de progression sont désactivés. Si
l'option est omise, `sectionSnap` reste actif quelle que soit la largeur.

Les panneaux restent écrits dans le template de la strate. Leur navigation est
produite avec `component('section-snap', ...)`. Leurs IDs restent uniques si la
démonstration est rendue plusieurs fois sur une même page. Chaque panneau mesure exactement
`calc(100vh - var(--header-height))`, sans marge entre les panneaux, et son
ancre l’aligne immédiatement sous le header fixe.

## Navigation verticale

Sur un appareil où le Lenis global est disponible :

- le snap reste inactif tant que le haut de `.strate-snap` n'a pas atteint la
  ligne définie par `var(--header-height)` ;
- au franchissement descendant de cette ligne, le scroll s'arrête
  immédiatement sur la première slide, alignée sous le header, sans transition
  corrective de quelques pixels ;
- une impulsion verticale de molette sélectionne la strate suivante ou
  précédente ;
- les faibles deltas d'un trackpad sont cumulés jusqu'au seuil de `40px` ;
- la transition dure `0.9s` avec une décélération en fin de course ;
- `lock: true` empêche plusieurs changements de slide pendant la transition ;
- `ArrowUp`, `ArrowDown`, `PageUp` et `PageDown` utilisent les mêmes cibles ;
- un déplacement effectué avec la scrollbar rejoint la strate la plus proche
  lorsque la scrollbar est relâchée ;
- le scroll redevient normal avant la première strate et après la dernière ;
- un conteneur interne scrollable conserve la priorité tant qu'il peut encore
  défiler dans la direction demandée ;
- une strate plus haute que le viewport défile naturellement avant que la
  cible suivante soit aimantée.

Le relâchement de la scrollbar utilise l'événement `scrollend`, avec un debounce
de repli pour les navigateurs qui ne le prennent pas en charge. Les positions
sont recalculées avec `ResizeObserver`. Lenis tient compte du
`scroll-margin-top` défini sur chaque strate.

## Progression visuelle `--i`

Chaque `.strate` reçoit une variable CSS `--i` actualisée pendant le scroll :

- `1` lorsque la strate est exactement placée sur sa cible ;
- une valeur intermédiaire pendant son arrivée ou sa sortie ;
- `0` lorsqu'elle atteint la position de la strate voisine.

La première slide constitue une exception : elle reste à `1` pendant l'arrivée
dans la strate snap, puis diminue de `1` à `0` en direction de la deuxième
slide. Elle est ainsi déjà complètement visible lorsque le conteneur entre dans
le viewport.

La dernière slide suit le comportement miroir : elle augmente de `0` à `1`
pendant son arrivée, puis reste à `1` lorsque le viewport quitte la strate snap.

La progression fonctionne dans les deux directions et peut piloter directement
un fondu :

```scss
.strate-snap > .strate {
    opacity: var(--i, 1);
}
```

La variable inline est retirée lors de la destruction du module.

## Parallaxe `--p`

Chaque slide reçoit aussi une progression signée indépendante de l'opacité :

- `1` avant que la slide atteigne sa cible ;
- `0` lorsqu'elle est placée ;
- `-1` après son passage.

La `.paralax` l'utilise sur ordinateur pour arriver depuis le bas et sortir
vers le haut :

```scss
.paralax {
    translate: 0 calc(var(--p, 0) * 8rem);
}
```

Le déplacement est désactivé avec `prefers-reduced-motion: reduce`. La variable
inline est retirée avec `--i` lors de la destruction du module.

Le changement de slide émet aussi l'événement `snap:change` sur la racine :

```js
root.addEventListener("snap:change", ({ detail }) => {
    console.log(detail.index, detail.slide);
});
```

## Navigation d'ancres

Le composant `section-snap` contrôle directement le menu `.snap-nav` qu’il
produit. Le menu suit la strate active pendant les transitions, ses
liens utilisent le même Lenis global et son curseur est recalculé avec le
layout. Le lien cible devient actif dès le lancement du snap, sans attendre la
fin de la transition, et reste verrouillé jusqu'à son terme. Il ne faut pas
initialiser un autre module de navigation sur ce menu.

## Replis et accessibilité

Le contrôleur ne capture aucun geste lorsque Lenis est absent. C'est notamment
le cas sur les pointeurs tactiles avec la configuration actuelle du projet.
Le document conserve alors son scroll vertical natif.

Avec `prefers-reduced-motion: reduce`, aucun geste n'est intercepté. Les
champs de formulaire, les zones `contenteditable`, les éléments portant
`data-snap-ignore`, `data-lenis-prevent` ou `data-lenis-prevent-wheel` gardent
également leur comportement normal.

Le composant retourne le lifecycle complet à l’hydrateur, qui peut suspendre ou
détruire ensemble le snap et sa navigation.

## Dépendances

- le Lenis global exposé dans `window.lenis` ;
- `assets/components/section-snap/section-snap.js` ;
- `assets/components/section-snap/section-snap.scss` ;
- `assets/strates/strate-snap/strate-snap.scss`.
