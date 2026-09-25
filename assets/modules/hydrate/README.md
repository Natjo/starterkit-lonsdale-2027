# Hydrate

Charge à la demande les modules JavaScript déclarés dans le HTML avec `data-module`, puis gère leur cycle de vie.

Son manifeste utilise `"activation": "always"` parce que `assets/app.js`
l’initialise globalement. Une page ou une strate ne doit donc pas le déclarer
comme dépendance uniquement pour empêcher Forge de le supprimer.

## Markup

```html
<section
    data-module="strates/strate-example"
    data-context="@visible true"
    data-module-media="(min-width: 768px)"
></section>
```

Les familles acceptées sont `common`, `components`, `heros`, `pages` et `strates`. Le fichier chargé doit porter le même nom que son dossier.

## Chargement

- sans `data-context`, le module est chargé immédiatement ;
- `data-context="@visible true"` attend que l’élément approche du viewport ;
- `data-module-media` attend que la media query corresponde ;
- les imports résolus et en cours sont mis en cache pour éviter les chargements multiples.

## Contrat d’un module

```js
export default function initExample(root) {
    const onClick = () => {};
    root.addEventListener("click", onClick);

    return {
        pause() {},
        resume() {},
        destroy() {
            root.removeEventListener("click", onClick);
        },
    };
}
```

Un module peut retourner une fonction de nettoyage, un lifecycle `{ pause, resume, destroy }`, plusieurs lifecycles dans un tableau ou `undefined`. Les destructions multiples sont sécurisées et les tableaux sont détruits dans l’ordre inverse.

## API

- `hydrate(container)` initialise les éléments `[data-module]` présents dans un conteneur ;
- `observe(container)` surveille les ajouts et suppressions dynamiques avec un `MutationObserver` ;
- `loadModule(name)` résout un module valide et mutualise son import.

Les modules exposant `pause` et `resume` sont suspendus hors écran. Lorsqu’un élément hydraté est retiré du DOM, ses observers, media queries et fonctions de nettoyage sont libérés.

## Robustesse

- un nom de module invalide ou un import impossible est journalisé sans interrompre la page ;
- sans `IntersectionObserver`, le chargement visible devient immédiat ;
- un élément supprimé pendant un import asynchrone n’est pas initialisé après coup ;
- chaque module reste limité à sa racine et doit nettoyer ses propres listeners, timers et RAF.
