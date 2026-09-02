# Composant `tag`

Affiche une catégorie ou un mot-clé informatif dans un `<span>`. Le composant
fonctionne uniquement avec `component('tag', [...])` et un payload associatif.

## Utilisation

```php
component('tag', [
    'name' => 'Actualités',
    'classes' => 'tag-featured',
    'attributes' => ['data-category' => 'news'],
]);
```

Les arguments positionnels et les listes directes ne sont
plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Texte du tag |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

## Règles de rendu

- Aucun HTML n’est produit si `name` est absent, vide ou invalide.
- Les espaces extérieurs du texte sont supprimés.
- Le contenu est échappé avec `esc_html()` : le HTML n’est jamais interprété.
- La classe `tag` est toujours présente.
- Les classes sont nettoyées et dédupliquées.
- L’attribut `class` est protégé.
- Aucun JavaScript n’est nécessaire.

## Tags interactifs

Le composant `tag` est uniquement informatif. Pour une action ou une
navigation ayant la même apparence, utiliser les composants sémantiques avec
la classe `tag`.

Bouton :

```php
component('btn', [
    'name' => 'Filtrer',
    'classes' => 'tag',
]);
```

Lien :

```php
component('link', [
    'link' => [
        'url' => '/design/',
        'title' => 'Design',
    ],
    'classes' => 'tag',
]);
```

## Dépendances

- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `esc_html()` pour sécuriser le contenu ;
- `assets/components/tag/tag.scss` pour les styles.
