# Composant `badge`

Génère un libellé court dans un élément `<span>`. Le composant fonctionne
uniquement avec `component('badge', [...])` et un payload associatif.

## Utilisation

```php
component('badge', [
    'name' => 'Nouveau',
    'classes' => 'badge-highlight',
    'attributes' => [
        'aria-label' => 'Nouveau contenu',
        'data-category' => 'featured',
    ],
]);
```

Les arguments positionnels, les listes directes et les
anciens alias `label`, `title` et `text` ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Libellé du badge |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

## Règles de rendu

- Aucun HTML n’est produit si `name` est absent, vide ou invalide.
- Les espaces extérieurs du libellé sont supprimés.
- Le libellé est échappé avec `esc_html()` : le HTML est affiché comme texte.
- La classe `badge` est toujours présente.
- Les classes sont nettoyées et dédupliquées.
- L’attribut `class` est protégé et ne peut pas être remplacé.
- Les autres attributs sûrs sont validés et échappés.
- Aucun JavaScript n’est nécessaire.

## Exemple

```php
component('badge', [
    'name' => 'À la une',
    'classes' => ['badge-featured', 'text-small'],
    'attributes' => ['data-category' => 'featured'],
]);
```

Produit :

```html
<span class="badge badge-featured text-small" data-category="featured">À la une</span>
```

## Dépendances

- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `esc_html()` pour sécuriser le libellé ;
- `assets/components/badge/badge.scss` pour les styles.
