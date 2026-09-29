# Strate `content-columns`

Affiche de une à trois colonnes éditoriales. Chaque colonne peut contenir un
titre, un texte riche et une image.

## Appel

```php
strate('content-columns', [
    'columns' => [
        [
            'title' => 'Première colonne',
            'text' => '<p>Contenu éditorial.</p>',
            'image' => 123,
        ],
    ],
]);
```

## Contrat

| Clé | Type | Rôle |
| --- | --- | --- |
| `columns` | `array` | De 1 à 3 colonnes ; les entrées supplémentaires sont ignorées |
| `columns[].title` | `mixed` | Transmis à `component('title')` |
| `columns[].text` | `mixed` | Transmis à `component('text')` |
| `columns[].image` | `ID\|URL\|null` | Transmis à `component('picture')` |
| `options` | `array` | Options communes traitées par `options()` |

Les colonnes entièrement vides ne sont pas rendues. Si aucune colonne ne
contient de donnée exploitable, la strate ne produit aucun HTML.

## Champs ACF

Le layout `content-columns` contient un repeater `columns`, obligatoire, avec
un minimum de 1 et un maximum de 3 lignes. Chaque ligne contient `title`,
`text` et `image`.

Le manifeste préremplit trois colonnes de démonstration. Leurs images dédiées
mesurent `960 × 640 px` et sont importées dans la médiathèque par Forge lors de
la création du site.

## Dépendances

- `component('picture')` ;
- `component('title')` ;
- `component('text')`.
