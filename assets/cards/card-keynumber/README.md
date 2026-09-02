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
