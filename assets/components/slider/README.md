# Composant `slider`

Génère un carrousel horizontal fondé sur le scroll natif et `scroll-snap`. Le
composant fonctionne uniquement avec `component('slider', [...])` et un payload
associatif.

## Mode card

```php
component('slider', [
    'items' => $items,
    'card' => 'news',
    'navigation' => true,
    'pagination' => true,
    'timeline' => false,
    'aria_label' => 'Actualités à découvrir',
]);
```

Chaque item doit être un ID de post positif ou un tableau associatif non vide
transmis à `card('news', $item)`. Le préfixe `card-` reste facultatif.

## Mode HTML direct

```php
component('slider', [
    'items' => [
        '<article>Premier contenu</article>',
        '<article>Deuxième contenu</article>',
    ],
    'card' => null,
    'aria_label' => 'Exemples',
]);
```

Les items directs doivent être des scalaires ou objets `Stringable`; leur HTML
est filtré avec `wp_kses_post()`.

Les arguments positionnels et les listes directes ne
sont plus supportés. `items` doit toujours être une véritable liste.

## Mode images responsive

```php
component('slider', [
    'items' => [
        [
            'images' => [
                'desktop' => 123,
                'mobile' => 456,
            ],
            'classes' => 'slider-image',
        ],
    ],
    'item_component' => 'picture',
    'card' => null,
]);
```

Le mode composant est volontairement limité à `picture`. Il permet de conserver
les balises `<picture>` et `<source>` générées par le composant, contrairement
au mode HTML filtré qui n’est pas destiné aux sources responsive.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `list<int\|array>` ou `list<scalar\|Stringable>` | `[]` | Cards en mode card, HTML filtré en mode direct |
| `card` | `scalar\|Stringable\|null` | `'card-news'` | Card utilisée ; `null` active le HTML direct |
| `item_component` | `'picture'\|null` | `null` | Rend chaque item avec le composant `picture` |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |
| `navigation` | `bool` | `true` | Boutons précédent/suivant |
| `pagination` | `bool` | `true` | Pagination générée par le module |
| `timeline` | `bool` | `false` | Barre de progression |
| `hydrate` | `bool` | `true` | Hydratation autonome |
| `media` | `scalar\|Stringable\|null` | `null` | Media query du chargeur |
| `aria_label` | `scalar\|Stringable` | `'Carrousel'` | Nom de la région et de la liste |
| `prev_label` | `scalar\|Stringable` | `'Diapositive précédente'` | Bouton précédent |
| `next_label` | `scalar\|Stringable` | `'Diapositive suivante'` | Bouton suivant |
| `pagination_label` | `scalar\|Stringable` | `'Navigation du carrousel'` | Groupe de pagination |
| `slide_label` | `scalar\|Stringable` | `'Diapositive'` | Nom générique d’une slide |
| `slide_position_format` | `scalar\|Stringable` | `'{label} {current} sur {total}'` | Format de position |
| `page_label` | `scalar\|Stringable` | `'Page'` | Annonce de page active |
| `go_to_label` | `scalar\|Stringable` | `'Aller à la page'` | Boutons de pagination |

Les booléens acceptent aussi `0`, `1`, `true`, `false`, `yes` et `no`.

En mode card, les posts et les images explicites de la collection sont
préchargés en lots avant le rendu. En mode `picture`, les IDs placés dans
`image`, `images.desktop` et `images.mobile` sont préchargés de la même manière.
Les URLs externes sont ignorées par ce préchargement et restent supportées.

Le composant ne contient aucune clé de traduction. Pour une autre langue :

```php
'slide_label' => 'Slide',
'slide_position_format' => '{label} {current} of {total}',
```

Les marqueurs `{label}`, `{current}` et `{total}` sont remplacés sans exécuter
le format comme une chaîne `sprintf`, ce qui évite les erreurs de format.

## Contrôles et hydratation

`navigation`, `pagination` et `timeline` contrôlent uniquement le markup. Ils
restent rendus avec `hydrate => false`, afin qu’un module parent puisse prendre
la main. Avec un seul item, les trois sont toujours omis.

```php
component('slider', [
    'items' => $items,
    'card' => 'news',
    'hydrate' => false,
    'navigation' => true,
    'pagination' => true,
    'timeline' => true,
]);
```

Pour un slider purement natif :

```php
component('slider', [
    'items' => $items,
    'card' => 'news',
    'hydrate' => false,
    'navigation' => false,
    'pagination' => false,
    'timeline' => false,
]);
```

## Strate dédiée

La strate transmet les options avec `slider_options`, force uniquement
`hydrate => false` sur le composant et devient propriétaire de son cycle de
vie :

```php
strate('slider', [
    'title' => 'À la une',
    'items' => $items,
    'card' => 'news',
    'slider_options' => [
        'navigation' => true,
        'pagination' => true,
        'timeline' => false,
    ],
]);
```

La strate appelle `add()` à l’entrée dans le viewport et `remove()` à la sortie
ou à sa destruction.

## Interaction et API JavaScript

Le drag suit le pointeur. Au relâchement, l’animation rejoint la page suivante
ou précédente sans déplacement brutal. Un changement rapide de direction
conserve la position, et les liens restent cliquables tant qu’aucun swipe réel
n’a eu lieu.

```js
import { Slider } from "./slider";

const slider = new Slider(element);
slider.onchange = (page, lastPage, progress) => {};
slider.onnext = (page) => {};
slider.onprev = (page) => {};
slider.add();
slider.slideTo(2);
slider.remove();
```

Les index correspondent aux pages de scroll, pas nécessairement aux items.
`remove()` débranche les listeners, observers, frames et contenus générés.
`prefers-reduced-motion` désactive le scroll animé.

## Accessibilité

- La racine est une région avec `aria-roledescription="carousel"`.
- La piste conserve une structure `list` / `listitem` valide.
- Chaque item est annoncé comme une slide avec sa position réelle.
- Les boutons natifs sont désactivés aux limites.
- La pagination expose la page active avec `aria-current="page"`.
- Les flèches clavier agissent lorsque la liste elle-même a le focus.
- Les attributs structurels, ARIA et ceux du module sont protégés.

## Dépendances

- `assets/components/slider/slider.js` ;
- `assets/components/slider/slider.scss` ;
- `card()` en mode card ;
- `component_classes()`, `component_attributes()` et `wp_kses_post()`.
