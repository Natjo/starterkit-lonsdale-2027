# Strate `pushes`

Affiche une sélection manuelle ou une remontée automatique de contenus sous
forme de cards.

Lire d’abord le [README général des strates](../README.md).

## Appel

Sélection manuelle :

```php
strate('pushes', [
    'title' => 'À la une',
    'mode' => 'manual',
    'post_type' => 'news',
    'card' => 'news',
    'items' => [123, 456],
]);
```

Remontée automatique :

```php
strate('pushes', [
    'text' => '<p>Les dernières actualités.</p>',
    'link' => [
        'url' => '/actualites/',
        'title' => 'Toutes les actualités',
        'target' => '',
    ],
    'mode' => 'automatic',
    'post_type' => 'news',
    'limit' => 3,
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien ACF interne ou externe |
| `mode` | `'manual'\|'automatic'` | `'manual'` | Source des contenus |
| `post_type` | `string` | `''` | CPT enregistré à interroger |
| `card` | `string` | Valeur de `post_type` | Card utilisée pour chaque contenu |
| `items` | `list<int>` | `[]` | IDs utilisés en mode manuel |
| `limit` | `int` | `3` | Nombre automatique, limité de 1 à 12 |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Par convention, un CPT `project` utilise `card-project`. La clé `card` permet
de remplacer cette convention pour un appel PHP. Le CPT doit être enregistré
et la card doit exister. Une strate sans introduction ni contenu valide ne
produit aucun balisage.

## Champs ACF

```text
acf_fc_layout = pushes
```

| Libellé | Identifiant | Type ACF | Condition |
| --- | --- | --- | --- |
| Titre | `title` | WYSIWYG léger | — |
| Texte | `text` | WYSIWYG | — |
| Lien | `link` | Lien | — |
| Mode | `mode` | Groupe de boutons | — |
| Type de contenu | `post_type` | Sélection | — |
| Contenus | `items` | Relation retournant des IDs | `mode = manual` |
| Nombre de contenus | `limit` | Nombre | `mode = automatic` |
| Options | `options` | Groupe cloné | — |

Le lien ACF accepte une destination interne ou externe. Le choix du CPT est
alimenté automatiquement avec les types de contenus possédant une card de même
nom. Le champ relation est limité aux mêmes types. Aucune valeur cachée ni
liste propre au projet n’est nécessaire dans le JSON ACF.

## Remontée automatique

La requête récupère les contenus publiés du plus récent au plus ancien. Elle
conserve les filtres multilingues, amorce le cache des objets WordPress et
précharge leurs métadonnées avant le rendu des cards. La sélection manuelle
utilise le même préchargement avant de valider le CPT de chaque ID.

Dans le starter, le couple `post_type = news` et `card-news` transforme le
groupe ACF `hero-news` en payload direct : date, titre, ID de l’image desktop
et permalien. Cette adaptation reste dans la strate afin que la card ne
dépende pas de la structure ACF du CPT.

## Assets et dépendances

La strate n’a pas d’asset propre. Elle utilise :

- `component('header', [...])` ;
- `component('list', [...])` ;
- `card($post_type, ...)` par défaut.
