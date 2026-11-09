# Card `card-keynumber`

Affiche un chiffre clé avec un suffixe, un titre et un texte facultatifs.

## Appel

```php
card('keynumber', [
    'value' => '98',
    'prefix' => '+',
    'suffix' => '%',
    'title' => 'de satisfaction',
    'text' => '<p>Mesurée auprès de nos clients.</p>',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `value` | `scalar\|Stringable` | `''` | Valeur visible obligatoire |
| `prefix` | `scalar\|Stringable` | `''` | Signe ou texte placé avant la valeur |
| `suffix` | `scalar\|Stringable` | `''` | Unité ou suffixe facultatif |
| `title` | `scalar\|Stringable` | `''` | Titre rendu en `h3` |
| `text` | `scalar\|Stringable` | `''` | Texte éditorial facultatif |

Une valeur vide ne produit aucun balisage. `prefix`, `value` et `suffix` sont
échappés comme du texte ; `title` et `text` délèguent leur filtrage aux
composants correspondants.

Cette card utilise normalement un payload personnalisé venant du répéteur ACF
de `strate-keynumbers`. Elle n’attend aucun post WordPress.

## Groupe ACF réutilisable

**Card - keynumber** contient un groupe `card` avec `value` (obligatoire),
`prefix`, `suffix`, `title` et `text` (WYSIWYG Basic). Il n’a pas de règle de
localisation : aucun CPT Keynumber n’est créé. On peut cloner ce groupe dans
un répéteur ACF ; la card et la strate Keynumbers acceptent alors les lignes
imbriquées `['card' => ['value' => '98', ...]]`. Le clone doit conserver le
nom `card` sans préfixer les noms de champs. Les anciens payloads plats restent
acceptés et les champs existants de la strate ne sont pas modifiés.

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
