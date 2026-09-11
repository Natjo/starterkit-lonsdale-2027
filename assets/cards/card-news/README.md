# Card `card-news`

Affiche une actualité. Cette card est rendue avec `card()` et non avec
`component()`.

Le manifeste `card-news.json` décrit les champs de contribution consommés par
WP Forge. Il doit rester synchronisé avec le payload documenté ci-dessous.

## Utilisation recommandée

### Depuis un post WordPress

```php
card('news', 123);
```

`123` doit être un entier strictement positif. Le dispatcher charge les champs
ACF du post, ajoute `post_id`, puis les transmet à la card.

### Depuis un contenu personnalisé

```php
card('news', [
    'date' => '22 juillet 2026',
    'datetime' => '2026-07-22',
    'title' => 'Titre de l’actualité',
    'text' => '<p>Résumé de l’actualité.</p>',
    'image' => 123,
    'image_size' => 'large',
    'link' => [
        'url' => '/actualites/titre/',
        'title' => 'Lire l’actualité',
        'target' => '_blank',
    ],
]);
```

Le payload doit être un tableau associatif non vide.

### Depuis un post avec des surcharges

```php
card('news', [
    'post_id' => 123,
    'title' => 'Titre remplacé pour ce rendu',
]);
```

Les champs ACF sont chargés en premier. Les valeurs fournies dans le tableau
les remplacent.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `post_id` | `int` | aucun | ID du post source, ajouté par `card()` |
| `date` | `scalar\|Stringable` | `''` | Date visible |
| `datetime` | `scalar\|Stringable` | `''` | Date machine au format HTML `datetime` |
| `title` | `scalar\|Stringable` | `''` | Titre rendu en `h3` avec la classe `title-3` |
| `text` | `scalar\|Stringable` | `''` | Résumé acceptant du HTML éditorial filtré |
| `image` | `ID\|URL` | aucun | Image principale rendue en mode `cover` |
| `image_size` | `string\|array{int,int}` | `large` | Taille transmise à `picture` |
| `link` | `array` | `[]` | Lien de la card |

### Structure de `link`

```php
'link' => [
    'url' => '/destination/',
    'title' => 'Libellé descriptif',
    'target' => '_blank',
],
```

Comportement actuel du template :

- `link.url` décide si `.card-content` est un lien `<a>` ou un `<div>` ;
- `link.title` n’est pas affiché ;
- `link.target` est appliqué lorsqu’il est fourni ;
- `_blank` ajoute `rel="noopener noreferrer"`.

## Champs ACF attendus

Pour un appel par ID, les noms de champs ACF doivent correspondre aux clés du
payload :

```text
date
datetime
title
text
image
image_size
link
```

`post_id` est fourni automatiquement et ne nécessite aucun champ ACF. Lorsqu’un
projet stocke ces données dans un groupe, l’appelant doit construire le payload
attendu par la card. `common/related` effectue par exemple le mapping du groupe
`hero-news`, dont l’image desktop est transmise à la clé `image`.

## Dans une liste

```php
component('list', [
    'items' => [123, 456],
    'card' => 'news',
]);
```

Les items peuvent aussi être des payloads personnalisés ou mélanger les deux
formes.

## Dans un slider

```php
component('slider', [
    'items' => [
        123,
        ['title' => 'Actualité personnalisée', 'text' => '<p>Résumé.</p>'],
    ],
    'card' => 'news',
]);
```

## Règles importantes

- Utiliser `card('news', ...)` ou `card('card-news', ...)`.
- Ne pas utiliser `component('card-news', ...)`.
- Un ID nul, négatif ou fourni sous forme de chaîne est refusé.
- Un tableau vide ou positionnel est refusé par le dispatcher.
- Le template produit actuellement son `<article>` même si toutes les valeurs
  du payload sont vides.
- Les classes et attributs personnalisés de l’élément `<article>` ne font pas
  partie du contrat actuel.
