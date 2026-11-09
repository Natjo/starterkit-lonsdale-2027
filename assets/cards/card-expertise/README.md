# Card `card-expertise`

Affiche une expertise avec un titre et un texte.

## Appel

```php
card('expertise', [
    'title' => 'Conseil stratégique',
    'text' => '<p>Nous accompagnons les transformations de marque.</p>',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre obligatoire rendu en `h3` |
| `text` | `scalar\|Stringable` | `''` | Texte éditorial facultatif |

Une card sans titre ne produit aucun HTML. Le texte accepte le HTML éditorial
autorisé par le composant `text`.

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])`.

## Champs ACF

Le groupe **Card - expertises** est affecté au CPT `expertise`.
Ses champs sont regroupés sous `card` : `card.text` (Description) et
`card.images.desktop` / `card.images.mobile` (IDs).
Le préparateur commun les lit directement, sans mapping de clés racine.
Le titre, l’extrait et l’image WordPress restent les replis. Les clés des
champs ACF existants sont conservées ; seul leur chemin est imbriqué.
Les payloads explicites du template restent plats (`title`, `text`, `image`).

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
