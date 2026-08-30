# Strate `keynumbers`

Affiche une introduction facultative suivie d’une liste de chiffres clés.
Chaque item est rendu par `card-keynumber`.

Lire d’abord le [README général des strates](../README.md) et le
[README de la card](../../cards/card-keynumber/README.md).

## Appel

```php
strate('keynumbers', [
    'title' => 'Nos chiffres clés',
    'items' => [
        [
            'value' => '25',
            'prefix' => '+',
            'suffix' => 'ans',
            'title' => 'd’expérience',
        ],
        [
            'value' => '98',
            'suffix' => '%',
            'title' => 'de satisfaction',
            'text' => '<p>Mesurée auprès de nos clients.</p>',
        ],
    ],
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien interne ou externe facultatif |
| `items` | `list<array>` | `[]` | Payloads transmis à `card-keynumber` |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Les items sans `value` sont ignorés. La strate ne produit aucun balisage
lorsqu’aucun chiffre valide n’est fourni.

## Champs ACF

```text
acf_fc_layout = keynumbers
```

| Libellé | Identifiant | Type ACF |
| --- | --- | --- |
| Titre | `title` | WYSIWYG léger |
| Texte | `text` | WYSIWYG |
| Lien | `link` | Lien |
| Chiffres clés | `items` | Répéteur |
| Valeur | `value` | Texte obligatoire |
| Préfixe | `prefix` | Texte |
| Suffixe | `suffix` | Texte |
| Titre | `title` | Texte |
| Texte | `text` | WYSIWYG |
| Options | `options` | Groupe cloné |

## Assets

La strate ne possède aucun JavaScript. Son SCSS organise la liste ; le rendu
de chaque item appartient à `card-keynumber`. Le design du starter reste
volontairement neutre : titre de strate en `h2`, titres des cards en `h3`,
couleur d’accent sur les valeurs et séparateur simple, sans image, icône,
fond décoratif ni animation.
