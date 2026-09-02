# Composant `link`

Génère un lien textuel sécurisé avec une icône décorative optionnelle. Le
composant fonctionne uniquement avec `component('link', [...])` et un payload
associatif.

## Utilisation

```php
component('link', [
    'link' => [
        'url' => '/projet/',
        'title' => 'En savoir plus',
        'target' => '',
    ],
    'classes' => 'link-2',
    'icon' => [
        'name' => 'blank',
        'width' => 16,
        'height' => 16,
    ],
    'attributes' => ['aria-label' => 'Découvrir le projet'],
]);
```

Avec un champ ACF de type `link` :

```php
component('link', [
    'link' => $args['link'] ?? [],
]);
```

Les arguments positionnels, les listes directes et la
forme plate `url`/`title` ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `link` | `array` | `[]` | Données `url`, `title` et `target` du lien |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `icon` | `array associatif` | absent | Payload de `component('icon', [...])` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

Une URL vide ou interdite, un titre vide ou un payload `link` invalide ne
produit aucun HTML.

## Target et rel

Les cibles `_self`, `_blank`, `_parent` et `_top` sont acceptées. Toute autre
valeur est ignorée. Avec `_blank`, `noopener noreferrer` est toujours ajouté.
Les tokens fournis dans `attributes['rel']` sont conservés et fusionnés.

```php
component('link', [
    'link' => [
        'url' => 'https://example.com',
        'title' => 'Site externe',
        'target' => '_blank',
    ],
    'attributes' => ['rel' => 'nofollow'],
]);
```

Le résultat contient `rel="nofollow noopener noreferrer"`.

## État désactivé

```php
component('link', [
    'link' => [
        'url' => '/destination/',
        'title' => 'Indisponible',
    ],
    'attributes' => ['aria-disabled' => true],
]);
```

Le texte et `aria-disabled` sont conservés, mais `href` et `target` sont
retirés.

## Icône

L’icône utilise exclusivement un payload associatif :

```php
'icon' => [
    'name' => 'blank',
    'width' => 16,
    'height' => 16,
    'classes' => 'link-icon',
]
```

Une liste positionnelle comme `['blank', 16, 16]` est ignorée. Comme l’icône
accompagne un texte visible, elle reste décorative par défaut.

## Règles de rendu

- La classe `link` est toujours présente.
- Le titre est échappé avec `esc_html()`.
- L’URL est sécurisée avec `esc_url()`.
- Les classes sont nettoyées et dédupliquées.
- `class`, `href`, `target` et `rel` sont protégés.
- Aucun JavaScript n’est nécessaire.

## Dépendances

- `normalize_action_link()` pour normaliser et sécuriser le lien ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/link/link.scss` pour les styles.
