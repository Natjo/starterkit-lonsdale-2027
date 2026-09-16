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
