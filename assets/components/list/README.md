# Composant `list`

Génère une liste `<ul>` dont chaque élément est rendu avec la même card.

## Utilisation

```php
component('list', [
    'items' => $articles,
    'card' => 'news',
    'type' => 'grid',
    'classes' => 'news-list',
]);
```

Seule cette API est supportée. Les arguments positionnels
ne sont pas acceptés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `list<int\|array>\|int\|array` | `[]` | IDs de posts ou contenus transmis aux cards |
| `card` | `scalar\|Stringable` | `'card-news'` | Nom de la card utilisée |
| `type` | `'grid'\|'flex'` | `'grid'` | Mode de mise en page et classe racine (`list` ou `list-flex`) |
| `classes` | `string\|array` | `''` | Classes supplémentaires du `<ul>` |
| `attributes` | `array` | `[]` | Attributs supplémentaires du `<ul>` |

Le préfixe `card-` est facultatif : `news` et `card-news` chargent tous les deux
la card `card-news`. Un nom invalide ou une card inexistante ne produit aucun
HTML.

Chaque item doit être un ID de post positif ou un tableau associatif non vide.
Les autres valeurs sont ignorées :

```php
component('list', [
    'items' => [
        123,
        ['title' => 'Premier article'],
        ['title' => 'Deuxième article'],
    ],
    'card' => 'news',
]);
```

Un tableau associatif peut être transmis comme item unique :

```php
component('list', [
    'items' => ['title' => 'Article unique'],
    'card' => 'news',
]);
```

Un ID unique est également accepté avec `'items' => 123`.

Une liste sans item valide ne produit aucun HTML. Utiliser ce composant
seulement lorsque tous les éléments partagent la même card.

## Mise en page

Le type `grid`, utilisé par défaut, génère la classe `list`. Le type `flex`
génère `list-flex` à sa place :

```php
component('list', [
    'items' => $articles,
    'card' => 'news',
    'type' => 'flex',
]);
```

Dans les deux modes, `--list-columns` définit le nombre maximal d'éléments par
ligne et vaut `1` par défaut. En mode flex, les éléments d'une ligne incomplète
s'étendent pour occuper l'espace disponible. `--list-gap` contrôle
l'espacement et utilise `--gap` par défaut.

Avant le rendu, le composant précharge en lots les posts référencés par un ID
ou une clé `post_id`, ainsi que les images explicitement présentes dans les
clés `image` et `images`. La déduplication est partagée avec les autres
collections pendant toute la requête PHP.

`class` et `role` sont réservés dans `attributes`, car ils sont gérés par le
composant. Les autres attributs sûrs (`id`, `aria-*`, `data-*`, etc.) sont
acceptés.
