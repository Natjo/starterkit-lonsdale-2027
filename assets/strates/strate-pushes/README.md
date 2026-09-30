# Strate `pushes`

Affiche, dans l’ordre choisi, des contenus existants sous forme de cards.
Lire d’abord le [README général des strates](../README.md).

```php
strate('pushes', [
    'title' => 'À la une',
    'items' => [123, 456],
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien ACF interne ou externe |
| `items` | `list<int>` | `[]` | IDs des contenus sélectionnés, dans l’ordre voulu |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Dans le BO, le layout flexible `pushes` propose uniquement les champs de
présentation, la liste « Contenus » (relation ACF) et les options communes.
La liste est limitée aux types ayant une card de même nom dans le starter.
Chaque contenu utilise la card associée à son type via
`theme_content_type_card`. Les IDs sont dédupliqués, les contenus sans card
ignorés. Il n’y a ni choix de mode, ni création de push manuel dans la strate.

Le payload provient de `theme_post_card_item` : groupe ACF `card` facultatif,
champs métier configurés, puis données WordPress. Sans introduction ni contenu
valide, la strate ne produit aucun balisage.

## Dépendances

La strate utilise `component('header')` et les cards associées aux types
sélectionnés. Son SCSS est co-localisé avec ce README.
Le manifeste préremplit l'introduction, mais laisse `items` vide : les IDs des
publications doivent provenir du site créé.
