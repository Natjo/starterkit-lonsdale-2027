# Composant `header`

Génère l’introduction d’une section avec un titre `h2` fixe, un texte éditorial
et un lien facultatif. Ce composant n’est pas le header principal du site : la
navigation globale reste dans `common/header-nav`.

## Utilisation

```php
component('header', [
    'title' => 'Nos expertises',
    'title_classes' => 'title-2',
    'text' => '<p>Découvrez nos domaines d’intervention.</p>',
    'link' => [
        'url' => '/expertises/',
        'title' => 'Toutes les expertises',
        'target' => '',
    ],
    'classes' => 'section-header',
]);
```

Seule cette API est supportée. Les arguments positionnels
et les anciens alias ne sont pas acceptés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre de la section |
| `text` | `scalar\|Stringable` | `''` | Texte éditorial |
| `link` | `array` | `[]` | Lien facultatif |
| `classes` | `string\|array` | `''` | Classes du `<header>` |
| `attributes` | `array` | `[]` | Attributs du `<header>` |
| `title_classes` | `string\|array` | `''` | Classes supplémentaires du titre |
| `title_attributes` | `array` | `[]` | Attributs supplémentaires du titre |
| `text_classes` | `string\|array` | `''` | Classes supplémentaires du texte |
| `text_attributes` | `array` | `[]` | Attributs supplémentaires du texte |

Les anciennes clés `titre`, `heading`, `headline`, `intro`, `content`,
`description`, `cta` et `button` ont été retirées.

## Lien

Le tableau `link` utilise directement la structure d’un lien ACF. Il peut aussi
transmettre les options du composant `link` :

```php
'link' => [
    'url' => '/expertises/',
    'title' => 'Toutes les expertises',
    'target' => '_blank',
    'classes' => 'header-link',
    'icon' => [
        'name' => 'blank',
        'width' => 16,
        'height' => 16,
    ],
    'attributes' => [
        'aria-label' => 'Voir toutes les expertises',
        'data-track' => 'expertises',
    ],
],
```

Le lien est rendu uniquement lorsque son URL est sûre et son libellé non vide.

## Niveau du titre

Le titre est toujours rendu en `h2`. La clé `hx` est volontairement ignorée :
le niveau fait partie du contrat de présentation du composant.

Si une autre hiérarchie est nécessaire, utiliser directement
`component('title', [...])`.

## Rendu

Au moins un titre, un texte ou un lien valide est nécessaire. Chaque donnée est
déléguée à son composant :

- `title` vers `component('title', [...])` ;
- `text` vers `component('text', [...])` ;
- `link` vers `component('link', [...])`.

Les sous-composants gèrent eux-mêmes les valeurs vides. `class` est réservé dans
`attributes`, car la classe du conteneur est construite par le composant.

Le `<header>` représente l’introduction d’une section, d’un article ou d’un
bloc de contenu. Il ne doit pas être placé dans un autre élément `<header>`.
