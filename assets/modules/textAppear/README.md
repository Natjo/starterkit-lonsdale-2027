# `textAppear.js`

Animation d’entrée de texte (split chars). Indépendant de `scrollDriver`.

Dépend de [`splitText`](../splitText/splitText.README.md) pour découper chaque
bloc en mots et graphèmes sans reconstruire son HTML inline.

Ajoute `text-appear-root`, puis **`is-animated`** sur `root` pour déclencher les
animations CSS sans dépendre d'une classe active globale non scopée.

Retourne une fonction **`destroy`** (idempotente) compatible avec `hydrate`.

## Concept

| Étape | Comportement |
|-------|--------------|
| Init | Normalise les `items`, optionnellement attend le viewport (`whenVisible`) |
| Split | `splitText` par bloc → `.word` / `.char` + vars CSS |
| Play | Double `requestAnimationFrame` puis `is-animated` sur `root` |
| Destroy | Annule rAF / IO et restaure le DOM, les classes et les styles inline d'origine |

Le double rAF garantit que l’état initial (opacité 0, `translate`) est peint avant le déclenchement CSS — évite les flashs sur `reveal`.

## Types

| `type` | Effet | CSS |
|--------|-------|-----|
| `reveal` | Slide up depuis le bas | `translate: 0 110%` → `0` |
| `fadein` | Opacité 0 → 1 | pas de translate |

## Markup attendu

```html
<header class="hero hero-homepage">
    <p class="eyebrow">…</p>
    <h1 class="title">…</h1>
    <p class="intro">…</p>
</header>
```

Les sélecteurs dans `items[].el` sont résolus **dans `root`** (section, hero, strate…).

## Usage — hero (init direct)

Animation immédiate au chargement (`whenVisible` par défaut `false`).

```js
import textAppear from "@modules/textAppear/textAppear";

const ITEMS = [
    { el: ".eyebrow", type: "reveal", delay: 0, skipSelector: ".icon", duration: 400, stagger: 18 },
    { el: ".title", type: "reveal", delay: 250, duration: 550, stagger: 24 },
    { el: ".intro", type: "fadein", delay: 450, duration: 220, stagger: 10 },
];

export default el => {
    if (!el) return;

    return textAppear(el, { items: ITEMS });
};
```

## Usage — strate (`data-module` + `hydrate`)

```php
<section data-module="strates/strate-example" data-context="@visible true">
```

### Strate seule

Retourner `destroy` directement :

```js
import textAppear from "@modules/textAppear/textAppear";

export default el => textAppear(el, {
    whenVisible: true,
    items: [
        { el: ".title", type: "reveal", delay: 0, duration: 550, stagger: 24 },
    ],
});
```

### Strate + scroll / listeners

Chaîner le destroy :

```js
const destroyText = textAppear(el, { whenVisible: true, items });

const destroy = () => {
    destroyText?.();
    stepCtrl.destroy();
};

return {
    pause: stepCtrl.pause,
    resume: stepCtrl.resume,
    destroy,
};
```

### `whenVisible` vs `data-context="@visible"`

| | `@visible` (hydrate) | `whenVisible` (textAppear) |
|---|----------------------|----------------------------|
| Rôle | Lazy-load du **module JS** | Lazy **split + play** de l’animation |
| Quand utiliser | Strates hors écran au boot | Animation déclenchée à l’entrée viewport même si le module est déjà chargé |

En pratique : `whenVisible: true` dans les strates pour ne pas splitter tant que l’utilisateur n’approche pas de la zone.

## Options

| Option | Défaut | Description |
|--------|--------|-------------|
| `items` | `[]` | Blocs à animer |
| `whenVisible` | `false` | Attendre le viewport avant split + play |
| `rootMargin` | `"100px 0px"` | Marge IO si `whenVisible` |
| `threshold` | `0` | Seuil IO si `whenVisible` |

### Item

| Champ | Description |
|-------|-------------|
| `el` / `node` / `selector` | Sélecteur ou élément (relatif à `root`) |
| `type` | `"fadein"` \| `"reveal"` |
| `delay` | Délai bloc (ms) → `--text-appear-delay` |
| `duration` | Durée (ms) → `--text-appear-duration` |
| `stagger` | Écart par char (ms) → `--text-appear-stagger` |
| `skipSelector` | Nœuds exclus du split (ex. `.icon`) |

Les durées, délais et staggers invalides ou négatifs sont ignorés. Si plusieurs
items ciblent le même élément, seul le premier est conservé. Les éléments DOM
fournis directement doivent appartenir à `root`.

## CSS

```scss
@import "modules/textAppear/textAppear";
```

Classes par bloc : `text-appear`, `text-appear--fadein`, `text-appear--reveal`.

Animations actives quand le parent possède à la fois `text-appear-root` et
`is-animated`.

Le module respecte toujours `prefers-reduced-motion` et ne splitte pas le texte
dans ce mode. Le SCSS conserve également un fallback de sécurité.

## Robustesse et nettoyage

- une cible, une option, un item ou un sélecteur invalide est ignoré ;
- sans `IntersectionObserver`, `whenVisible` démarre immédiatement ;
- le callback visible déconnecte l'observer avant le split ;
- les classes et variables CSS présentes avant l'initialisation sont restaurées
  avec leur valeur et leur priorité d'origine ;
- le nettoyage est idempotent, y compris sans item ou en mode animations
  réduites ;
- une erreur d'initialisation restaure les blocs déjà traités et laisse le
  contenu visible.

## Performance

- **`whenVisible: true`** : le split DOM (coûteux) est différé jusqu’à l’IO
- **Double rAF** : un seul batch par instance, pas de reflow en boucle
- **`destroy` idempotent** : safe si `hydrate` rappelle ou PJAX
- **Pas de ScrollDriver** : zéro listener scroll pour ce module
- **`prefers-reduced-motion`** : early exit avant split (JS + CSS)
- **Après lecture** : les spans restent dans le DOM sans RAF ni listener actif
  jusqu'au `destroy`

## `textAppear` vs `textAnimated`

| | **textAppear** | **textAnimated** |
|---|----------------|------------------|
| Déclencheur | Entrée / viewport | Scroll (stagger progressif) |
| Cible | Blocs `items[]` | `.text-animated` unique |
| Classe root | `is-animated` | — |
| ScrollDriver | Non | Oui |
| Lifecycle | `destroy` | `destroy` |
