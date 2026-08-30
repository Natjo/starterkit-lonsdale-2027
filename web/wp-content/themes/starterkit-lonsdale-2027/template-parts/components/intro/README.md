# Composant `intro`

Génère un texte d’introduction éditorial.

```php
component('intro', [
    'text' => '<p>Texte d’introduction.</p>',
    'classes' => 'featured',
    'attributes' => ['id' => 'page-intro'],
]);
```

Seule cette API est supportée. Les arguments
positionnels ne sont pas acceptés.

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `text` | `scalar\|Stringable` | `''` | Contenu obligatoire |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Une valeur vide ou complexe ne produit aucun HTML. Le contenu passe par
`wp_kses_post()` afin de conserver uniquement le HTML éditorial autorisé.
`class` est réservé dans `attributes`.
