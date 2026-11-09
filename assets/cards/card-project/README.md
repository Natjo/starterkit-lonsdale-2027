# Card `card-project`

Affiche un projet avec son image, sa date et son résumé.

## Appel

```php
card('project', [
    'post_id' => 123,
    'type' => 'Project',
    'date' => '16 septembre 2026',
    'datetime' => '2026-09-16',
    'title' => 'Projet exemple',
    'text' => '<p>Présentation facultative.</p>',
    'image' => 123,
    'image_size' => 'large',
    'link' => [
        'url' => '/projects/projet-exemple/',
        'title' => 'Voir le projet',
    ],
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `post_id` | `int` | aucun | Identifiant facultatif du contenu source |
| `type` | `scalar\|Stringable` | `''` | Type de contenu affiché en surtitre |
| `date` | `scalar\|Stringable` | `''` | Date visible |
| `datetime` | `scalar\|Stringable` | `''` | Date machine au format HTML `datetime` |
| `title` | `scalar\|Stringable` | `''` | Titre obligatoire rendu en `h3` |
| `text` | `scalar\|Stringable` | `''` | Résumé éditorial facultatif |
| `image` | `ID\|URL` | aucun | Image principale rendue en mode `cover` |
| `image_size` | `string\|array{int,int}` | `large` | Taille transmise à `picture` |
| `link` | `array` | `[]` | URL, libellé et cible facultative |

Une card sans titre ne produit aucun HTML. Une URL absente remplace le lien par
un `<div>`. La cible `_blank` ajoute `rel="noopener noreferrer"`.

Le template Hub construit explicitement ce payload depuis WordPress. La card
ne lit aucun champ ACF implicitement.

## Dépendances

- `component('eyebrow', [...])` ;
- `component('date', [...])` ;
- `component('picture', [...])` ;
- `component('title', [...])` ;
- `component('text', [...])`.

## Champs ACF

Le groupe **Card - project** est affecté au CPT `project`.
Ses champs sont regroupés sous `card` : `card.text` (Description) et
`card.images.desktop` / `card.images.mobile` (IDs).
Le préparateur commun les lit directement, sans mapping de clés racine.
Le titre, l’extrait et l’image WordPress restent les replis. Les clés des
champs ACF existants sont conservées ; seul leur chemin est imbriqué.
Les payloads explicites du template restent plats (`title`, `text`, `image`).

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
