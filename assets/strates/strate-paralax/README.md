# Strate `paralax`

Strate statique réservée au styleguide. Elle superpose un titre centré à une
image décorative plein écran et déplace légèrement cette image pendant la
traversée de la section.

```php
strate('paralax');
```

## Contrat

- la hauteur est de `100svh`, avec fallback `100vh` ;
- l'image est surdimensionnée de `10vh` en haut et en bas pour absorber son
  déplacement sans découvrir le fond ;
- le titre reste centré et ne se déplace pas ;
- l'image est décorative : `alt=""` et conteneur `aria-hidden="true"` ;
- le contenu et l'image sont écrits directement dans le template ;
- les arguments transmis sont ignorés ;
- la strate n'est pas déclarée dans ACF.

## Animation et hydratation

La strate utilise `scrollAnimation()` avec la plage `bottom-top`. La variable
`--paralax-y` évolue de `-10vh` à `10vh` pendant la traversée.

Le lifecycle `{ pause, resume, destroy }` est transmis directement à
`hydrate` :

- hors écran, la strate est retirée du singleton `ScrollDriver` et
  `will-change` est désactivé avec la classe `.viewed` ;
- lorsqu'elle revient, elle est réinscrite sans doublon ;
- à la destruction, `scrollAnimation()` nettoie automatiquement la variable
  CSS et les caches associés.

Avec `prefers-reduced-motion: reduce`, `scrollAnimation()` n'inscrit pas la
strate dans le driver et le CSS force une image immobile. Aucun contrôle local
n'est nécessaire dans le module de la strate.
