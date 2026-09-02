# Composant `eyebrow`

Génère un surtitre éditorial avec une icône décorative optionnelle. Le
composant fonctionne uniquement avec `component('eyebrow', [...])` et un
payload associatif.

## Utilisation

```php
component('eyebrow', [
    'eyebrow' => 'Notre <strong>expertise</strong>',
    'element' => 'div',
    'icon' => 'star',
    'icon_width' => 15,
    'icon_height' => 15,
    'classes' => 'text-appear',
    'attributes' => ['id' => 'hero-eyebrow'],
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `eyebrow` | `scalar\|Stringable` | `''` | Contenu du surtitre |
| `element` | `'div'\|'p'\|'span'` | `'div'` | Balise du conteneur |
| `icon` | `string` | `''` | Nom de l’icône décorative |
| `icon_width` | `int\|numeric-string` | `15` | Largeur de l’icône |
| `icon_height` | `int\|numeric-string` | `15` | Hauteur de l’icône |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Les anciens alias `suptitle`, `sup_title` et `html_tag` ne sont plus
interprétés. Toute donnée externe doit être explicitement mappée :

```php
component('eyebrow', [
    'eyebrow' => $args['suptitle'] ?? '',
]);
```

## Règles de rendu

- Aucun HTML n’est produit si `eyebrow` est absent, vide ou invalide.
- Le contenu est filtré avec `wp_kses_post()`.
- Une balise invalide est remplacée par `div`.
- La classe `eyebrow` est toujours présente.
- La classe `hasicon` est ajoutée lorsqu’une icône valide est fournie.
- L’icône est rendue avec `component('icon', [...])` et reste décorative.
- Les dimensions d’icône sont limitées à un minimum de `1`.
- Les classes sont nettoyées et dédupliquées.
- L’attribut `class` est réservé au composant.

## Exemples

Surtitre simple :

```php
component('eyebrow', [
    'eyebrow' => 'Notre expertise',
]);
```

Élément inline avec icône :

```php
component('eyebrow', [
    'eyebrow' => 'Nouveauté',
    'element' => 'span',
    'icon' => 'star',
]);
```

## Dépendances

- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `wp_kses_post()` pour le contenu éditorial.
