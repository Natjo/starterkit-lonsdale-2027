# Composant `select-lang`

Génère un sélecteur de langue accessible sous forme de bouton de divulgation et
de liste de liens.

```php
component('select-lang', [
    'languages' => [
        ['code' => 'fr', 'label' => 'Français', 'url' => '/fr/', 'current' => true],
        ['code' => 'en', 'label' => 'English', 'url' => '/en/'],
        ['code' => 'de', 'label' => 'Deutsch', 'url' => '/de/', 'disabled' => true],
    ],
    'label' => 'Choisir la langue',
]);
```

Seule cette API est supportée. Les arguments
positionnels et les listes directes ne sont pas acceptés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `languages` | `list<array>` | `[]` | Langues disponibles, obligatoire |
| `label` | `scalar\|Stringable` | `'Choisir la langue'` | Libellé accessible |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Chaque langue utilise exclusivement les clés suivantes :

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `code` | `scalar\|Stringable` | `''` | Code de langue BCP 47 simplifié |
| `label` | `scalar\|Stringable` | code en majuscules | Libellé visible |
| `url` | `scalar\|Stringable` | `''` | URL obligatoire |
| `current` | `bool\|string` | `false` | Langue courante |
| `disabled` | `bool\|string` | `false` | Langue indisponible |

Les anciens alias `args`, `selected` et `active` ont été retirés. Une entrée
sans libellé ou avec une URL refusée par `esc_url()` est ignorée. La première
langue valide sert de valeur courante si aucune langue ne porte `current`.

Le module JavaScript gère le clic, `Escape`, les flèches, `Home`, `End`, la
fermeture au clic extérieur et la restitution du focus. `class`, `data-module`
et `data-context` sont réservés dans `attributes`.
