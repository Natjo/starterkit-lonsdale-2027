# Composant `marquee`

Génère un défilement horizontal infini, draggable et accessible. Le composant
fonctionne uniquement avec `component('marquee', [...])` et un payload
associatif.

## Utilisation

```php
component('marquee', [
    'items' => [
        '<span>Stratégie</span>',
        '<a href="/design/">Design</a>',
        '<span>Technologie</span>',
    ],
    'speed' => 60,
    'direction' => 'left',
    'scroll_acceleration' => true,
    'follow_scroll_direction' => true,
    'pause_on_hover' => true,
    'pause_on_focus' => true,
    'aria_label' => 'Nos expertises',
]);
```

Les arguments positionnels, les listes directes et les
items sous forme de tableaux `content`/`text` ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `list<scalar\|Stringable\|int\|array>` | `[]` | HTML, cards ou payloads `picture` selon le mode |
| `card` | `scalar\|Stringable\|null` | `null` | Card utilisée pour les items structurés |
| `item_component` | `'picture'\|null` | `null` | Rend chaque item avec `picture` |
| `speed` | `int\|float\|numeric-string` | `60` | Vitesse en pixels par seconde, entre `10` et `400` |
| `direction` | `'left'\|'right'` | `'left'` | Direction initiale |
| `hydrate` | `bool` | `true` | Active l’hydratation autonome |
| `scroll_acceleration` | `bool` | `true` | Accélère selon la vélocité de Lenis |
| `follow_scroll_direction` | `bool` | `true` | Adapte le sens à celui du scroll |
| `pause_on_hover` | `bool` | `true` | Pause au survol |
| `pause_on_focus` | `bool` | `true` | Pause lorsqu’un enfant reçoit le focus |
| `aria_label` | `scalar\|Stringable` | `'Contenu défilant'` | Nom accessible de la région |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Une valeur booléenne peut aussi être transmise sous forme de chaîne `"true"`,
`"false"`, `"1"` ou `"0"`.

## Contenu HTML, images et cards

Chaque item doit être une chaîne ou un objet `Stringable`. Le HTML est filtré
avec `wp_kses_post()`.

Pour des cards :

```php
component('marquee', [
    'items' => [123, 456],
    'card' => 'news',
]);
```

Pour des images :

```php
component('marquee', [
    'items' => [
        [
            'image' => 123,
            'size' => 'large',
            'fit' => 'cover',
        ],
    ],
    'item_component' => 'picture',
]);
```

Le mode `picture` évite de filtrer les balises structurelles internes du
composant. Les autres noms de composants sont ignorés.

## Hydratation avec une strate

Le composant s’hydrate seul par défaut. Une strate qui importe `initMarquee`
doit passer `hydrate => false` afin de rester seule propriétaire du cycle de
vie :

```php
component('marquee', array_replace($marquee_options, [
    'items' => $args['items'] ?? [],
    'hydrate' => false,
]));
```

## Comportement

- Le drag souris et tactile suit le pointeur.
- Le relâchement conserve une inertie fluide avant de retrouver la vitesse
  normale.
- La direction du swipe devient la nouvelle direction de défilement.
- Un clic simple sur un lien reste actif ; un clic après déplacement est
  annulé.
- `pointercancel` et le redimensionnement annulent proprement le drag.
- `setDirection('left'|'right')` permet de changer la direction en JavaScript.
- `prefers-reduced-motion` désactive l’animation.

Avec `scroll_acceleration`, la vélocité de Lenis augmente temporairement la
vitesse. Lorsque `follow_scroll_direction` est actif, un scroll montant inverse
le sens de référence et un scroll descendant le restaure.

## Cycle de vie et performances

- L’animation et les observations sont suspendues hors écran.
- `ResizeObserver`, `window.resize` et l’écoute Lenis sont déconnectés pendant
  la pause et au `destroy()`.
- Les recalculs planifiés et l’inertie RAF sont annulés au nettoyage.
- Le RAF n’est utilisé que pendant l’inertie, jamais en continu.
- Les clones ne sont reconstruits que lorsque les dimensions l’exigent.
- Une seule `CSSAnimation` est conservée et son `playbackRate` est ajusté.
- `will-change: transform` est actif uniquement lorsque le marquee est prêt et
  actif.
- `contain: paint` limite les invalidations de peinture.

L’instance expose `pause()`, `resume()` et `destroy()` pour l’hydratation.

## Attributs protégés

Le composant contrôle `class`, `role`, `aria-label`, ses attributs `data-marquee-*`,
`data-module` et `data-context`. Les autres attributs sûrs sont conservés.

## Dépendances

- `assets/components/marquee/marquee.js` pour l’interaction ;
- `assets/components/marquee/marquee.scss` pour le rendu ;
- Lenis, uniquement lorsque l’accélération au scroll est activée ;
- `component_classes()`, `component_attributes()` et `wp_kses_post()`.
