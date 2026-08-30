# Composant `title`

Génère un titre `h1` à `h6`. Le HTML autorisé par WordPress est conservé et la
classe CSS `title` est toujours ajoutée.

Le composant utilise uniquement un payload associatif.

## API recommandée

```php
component('title', [
    'title' => 'Titre de la section',
    'hx' => 2,
    'classes' => 'title-2',
    'attributes' => [
        'id' => 'section-title',
        'aria-label' => 'Titre de la section',
    ],
]);
```

Le composant `title` fonctionne uniquement avec cette API. ## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Contenu principal |
| `hx` | `int\|numeric-string` | `2` | Niveau sémantique |
| `classes` | `string\|array` | `''` | Classes ajoutées à `title` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

Une valeur `title` vide ou invalide ne produit aucun titre.

## Appel depuis une strate

Lorsque la strate contient déjà son titre, compléter ou remplacer ses options
avant l'appel :

```php
component('title', array_replace($args, [
    'hx' => 2,
    'classes' => 'title-2',
]));
```

## Règles de rendu

- les niveaux sont limités entre `1` et `6` ;
- un niveau invalide produit un `h2` ;
- le contenu passe par `wp_kses_post()` ;
- les classes sont nettoyées et dédupliquées ;
- les attributs sont validés et échappés ;
- `class` ne peut pas être remplacé via `attributes` ;
- un payload ou un contenu invalide ne produit aucun HTML.

## Exemple de HTML filtré

```php
component('title', [
    'title' => 'Une <strong>actualité</strong>',
    'hx' => 2,
    'classes' => 'title-2',
]);
```

Produit :

```html
<h2 class="title title-2">Une <strong>actualité</strong></h2>
```

## Dépendances

- `component_classes()` ;
- `component_attributes()` ;
- `wp_kses_post()`.
