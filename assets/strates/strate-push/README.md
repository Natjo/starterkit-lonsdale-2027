# Strate `push`

Affiche un contenu de mise en avant composé d’un titre, d’un texte riche, d’un
lien et d’une image.

## Appel

```php
strate('push', [
    'title' => 'Découvrir le projet',
    'text' => '<p>Une courte présentation du contenu mis en avant.</p>',
    'link' => [
        'url' => '/projet/',
        'title' => 'Voir le projet',
        'target' => '',
    ],
    'image' => 123,
]);
```

## Contrat

| Clé | Type | Rôle |
| --- | --- | --- |
| `title` | `mixed` | Titre transmis au composant `header` |
| `text` | `mixed` | Texte riche transmis au composant `header` |
| `link` | `array` | Lien ACF transmis au composant `header` |
| `image` | `ID\|URL\|null` | Image transmise au composant `picture` |
| `options` | `array` | Options communes traitées par `options()` |

La strate ne produit aucun HTML lorsque les quatre contenus sont vides.

Le titre et le texte Lorem viennent des `default_value` ACF. Le manifeste
préremplit le lien avec « Découvrir » et fournit un placeholder dédié de
`1460 × 973 px`, adapté au format WordPress `730_auto` en densité 2×. Forge
importe cette image dans la médiathèque lors de la création du site.

## Champs ACF

Le layout flexible `push` contient les champs `title`, `text`, `link` et
`image`, suivis des options communes des strates.

## Dépendances

- `component('header')` ;
- `component('picture')`.
