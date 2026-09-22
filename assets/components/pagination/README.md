# Composant `pagination`

Affiche une pagination WordPress accessible à partir d’un nombre total de pages
et de la page courante.

## Appel

```php
global $wp_query;

component('pagination', [
    'total' => (int) $wp_query->max_num_pages,
    'current' => max(1, (int) get_query_var('paged')),
    'prev_text' => 'Précédent',
    'next_text' => 'Suivant',
    'aria_label' => 'Pagination des actualités',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `total` | `int\|numeric-string` | `0` | Nombre total de pages |
| `current` | `int\|numeric-string` | `1` | Page courante, limitée à la plage disponible |
| `prev_text` | `scalar\|Stringable` | `‹` | Libellé du lien précédent |
| `next_text` | `scalar\|Stringable` | `›` | Libellé du lien suivant |
| `aria_label` | `scalar\|Stringable` | `Pagination` | Nom accessible du `<nav>` |
| `base` | `scalar\|Stringable` | `''` | Base facultative contenant `%#%`, utile pour une pagination par query string |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires du `<nav>` |

Le composant ne produit aucun HTML lorsque `total` vaut `0` ou `1`. Les
libellés éditoriaux sont fournis par l’appelant et ne sont pas traduits dans le
composant.

## Rendu et sécurité

- les liens sont produits par `paginate_links()` ;
- une base explicite est utilisée uniquement si elle contient le placeholder `%#%` ;
- seuls les éléments `a` et `span` attendus sont conservés ;
- `class` et `aria-label` sont réservés ;
- les classes et attributs libres utilisent les helpers communs.

## Dépendances

- `paginate_links()` ;
- `component_classes()` ;
- `component_attributes()`.
