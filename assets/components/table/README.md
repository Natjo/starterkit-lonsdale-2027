# Composant `table`

Génère une table de données accessible et responsive, sans JavaScript. Le
composant fonctionne uniquement avec `component('table', [...])` et un payload
associatif.

## Utilisation

```php
component('table', [
    'caption' => 'Résultats',
    'headers' => ['Nom', 'Valeur'],
    'rows' => [
        ['Alpha', '10'],
        ['Beta', '20'],
    ],
]);
```

Les arguments positionnels et les listes directes ne sont
plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `headers` | `array` | `[]` | Colonnes obligatoires |
| `rows` | `list<array>` | `[]` | Lignes obligatoires |
| `id` | `scalar\|Stringable` | auto | Base des identifiants |
| `caption` | `scalar\|Stringable` | `''` | Caption visible |
| `aria_label` | `scalar\|Stringable` | `'Tableau de données'` | Caption masquée par défaut |
| `row_headers` | `bool` | `false` | Transforme la première cellule en en-tête de ligne |
| `scrollable` | `bool` | `true` | Rend la zone de défilement focalisable |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `table_classes` | `string\|array` | `''` | Classes du `<table>` |
| `attributes` | `array` | `[]` | Attributs du conteneur |
| `scroll_attributes` | `array` | `[]` | Attributs de la zone de défilement |
| `table_attributes` | `array` | `[]` | Attributs du `<table>` |
| `caption_attributes` | `array` | `[]` | Attributs du `<caption>` |

Les chaînes `"true"`, `"false"`, `"1"` et `"0"` sont normalisées pour les
options booléennes. Aucun appel de traduction n’est effectué dans le composant.

## Colonnes structurées

Une colonne simple est une chaîne. Une colonne structurée utilise strictement
la clé `label` :

```php
'headers' => [
    'name' => [
        'label' => 'Nom',
        'scope' => 'col',
        'classes' => 'column-name',
        'attributes' => ['data-column' => 'name'],
    ],
    'value' => [
        'label' => 'Valeur',
        'colspan' => 2,
    ],
],
```

La clé associative (`name`, `value`) permet de lire les lignes indépendamment
de l’ordre de leurs données. Les anciens alias `title` et `name` du libellé ne
sont plus interprétés. `scope` accepte `col` ou `colgroup`; `colspan` et
`rowspan` sont limités de `1` à `100`.

## Lignes et cellules structurées

Une ligne peut être séquentielle ou suivre les clés des colonnes :

```php
'rows' => [
    ['Alpha', '10'],
    ['value' => '20', 'name' => 'Beta'],
],
```

Une cellule structurée utilise strictement `content` :

```php
[
    'content' => '<strong>Alpha</strong>',
    'header' => true,
    'scope' => 'row',
    'colspan' => 1,
    'rowspan' => 1,
    'classes' => 'is-highlighted',
    'attributes' => ['data-value' => 'alpha'],
]
```

Les anciens alias `value` et `text` ne sont plus interprétés. `scope` accepte
`row` ou `rowgroup`.

Une ligne peut séparer ses cellules de ses attributs :

```php
[
    'cells' => ['name' => 'Alpha', 'value' => '10'],
    'attributes' => ['data-row' => 'alpha'],
]
```

## Caption et défilement

Une balise `<caption>` est toujours présente. Lorsque `caption` est vide,
`aria_label` est rendu dans une caption visuellement masquée.

Avec `scrollable => true`, la zone reçoit `tabindex="0"`, `role="region"` et
`aria-labelledby`. Utiliser `false` seulement lorsqu’aucun débordement
horizontal n’est possible.

La largeur minimale peut être ajustée localement :

```php
'attributes' => ['style' => '--table-min-width: 48rem'],
```

## Normalisation et sécurité

- Une colonne sans libellé est ignorée.
- Une ligne non-tableau ou entièrement vide est ignorée.
- Une cellule manquante produit une cellule vide pour préserver la grille.
- Une cellule excédentaire sans colonne correspondante est ignorée.
- Le HTML des en-têtes et cellules est filtré avec `wp_kses_post()`.
- Les identifiants et attributs structurels sont protégés.
- Les classes sont nettoyées et dédupliquées.

Pour des en-têtes sur plusieurs lignes ou des associations très complexes,
préférer un template métier dédié.

## Dépendances

- `component_classes()` et `component_attributes()` ;
- `wp_kses_post()` pour les contenus éditoriaux ;
- `assets/components/table/table.scss` pour le rendu responsive.
