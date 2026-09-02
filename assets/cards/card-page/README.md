# Card `card-page`

Affiche un résultat de page ou le fallback d’un type de contenu ne possédant
pas de card dédiée.

## Appel

```php
card('page', [
    'post_id' => 123,
    'type' => 'Page',
    'title' => 'À propos',
    'text' => '<p>Présentation facultative.</p>',
    'link' => [
        'url' => '/a-propos/',
        'title' => 'Voir la page',
    ],
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `post_id` | `int` | aucun | Identifiant facultatif du contenu source |
| `type` | `scalar\|Stringable` | `''` | Type de contenu affiché en surtitre |
| `title` | `scalar\|Stringable` | `''` | Titre obligatoire rendu en `h3` |
| `text` | `scalar\|Stringable` | `''` | Résumé éditorial facultatif |
| `link` | `array` | `[]` | URL, libellé et cible facultative |

Une card sans titre ne produit aucun HTML. Une URL absente remplace le lien par
un `<div>`. La cible `_blank` ajoute `rel="noopener noreferrer"`.

La page de recherche construit explicitement ce payload depuis WordPress. Les
enrichissements ACF propres à un CPT passent par le filtre
`theme_search_result_item` déclaré dans `inc/config/search.php`. La card ne lit
aucun groupe ACF implicitement.

## Dépendances

- `component('eyebrow', [...])` ;
- `component('title', [...])` ;
- `component('text', [...])`.
