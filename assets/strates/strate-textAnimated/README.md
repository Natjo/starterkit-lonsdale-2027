# Strate `textAnimated`

Strate statique réservée au styleguide. Elle présente un titre dont les
caractères se révèlent progressivement en fonction du scroll grâce au module
`textAnimated`.

```php
strate('textAnimated');
```

## Contrat

- la strate ignore tous les arguments transmis ;
- son contenu est volontairement écrit directement dans le template ;
- elle n'est pas déclarée dans le flexible content ACF ;
- le premier descendant `.text-animated` est la cible de l'animation ;
- avec `prefers-reduced-motion: reduce`, le titre reste lisible et n'est pas
  découpé en caractères.

## Hydratation

La strate possède son cycle de vie avec
`data-module="strates/strate-textAnimated"`. Son module JavaScript importe
`textAnimated`, lui transmet la racine de la strate et retourne directement
son lifecycle `{ pause, resume, destroy }` à l'hydrateur.

Hors écran, la strate est retirée du singleton `ScrollDriver`. Lorsqu'elle
revient, elle est réinscrite sans doublon. `destroy()` restaure définitivement
le DOM original.

Le module partagé gère le découpage avec `splitText`, la progression avec
`ScrollDriver` et la restauration du DOM au nettoyage. Ne pas ajouter une
seconde hydratation sur le titre.

## Fichiers

```text
template-parts/strates/strate-textAnimated.php
assets/strates/strate-textAnimated/strate-textAnimated.scss
assets/strates/strate-textAnimated/strate-textAnimated.js
assets/modules/scrollDriver/textAnimated/textAnimated.js
```
