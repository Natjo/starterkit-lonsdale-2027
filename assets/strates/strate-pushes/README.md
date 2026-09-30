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
| `card` | `string` | Card associée au CPT, puis `post_type` | Card utilisée pour chaque contenu |
| `items` | `list<int>` | `[]` | IDs utilisés en mode manuel |
| `limit` | `int` | `3` | Nombre automatique, limité de 1 à 12 |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

La card par défaut suit le filtre `theme_content_type_card`, comme les autres
listings du thème. La clé `card` permet de la remplacer pour un appel PHP. Le
CPT doit être enregistré et la card doit exister. Une strate sans introduction
ni contenu valide ne produit aucun balisage.

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
conserve les filtres multilingues et précharge les posts et leurs métadonnées.
La sélection manuelle valide le CPT de chaque ID en conservant son ordre.
Les deux modes transmettent aux cards le même payload normalisé que
`Theme_Listing`, personnalisable avec `theme_post_card_item`. Le groupe ACF
facultatif `push` d’un contenu peut remplacer titre, description, date et image
champ par champ ; les autres valeurs viennent de WordPress.

## Assets et dépendances

La strate possède un fichier SCSS. Elle utilise aussi :

- `component('header', [...])` ;
- `component('list', [...])` ;
- la card associée au CPT par défaut.
