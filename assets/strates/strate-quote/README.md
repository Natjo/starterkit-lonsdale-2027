# Strate `quote`

Affiche une citation éditoriale avec une image, un auteur et une source
facultatifs.

Lire d’abord le [README général des strates](../README.md), puis celui du
[composant `quote`](../../components/quote/README.md).

## Appel

```php
strate('quote', [
    'text' => '<p>Une citation éditoriale.</p>',
    'image' => 123,
    'author' => 'Prénom Nom',
    'source' => 'Publication',
    'url' => '/publication/',
]);
```

## Contrat

| Clé | Type | Valeur vide | Rôle |
| --- | --- | --- | --- |
| `text` | `mixed` | Citation Lorem ipsum | Transmis à la clé `quote` du composant |
| `image` | `ID\|URL\|null` | Portrait du manifeste | Image facultative |
| `author` | `scalar\|Stringable` | `Jane Doe` | Nom facultatif de l’auteur |
| `source` | `scalar\|Stringable` | `Lorem Magazine` | Publication ou œuvre facultative |
| `url` | `scalar\|Stringable` | `https://example.com` | URL facultative de la source |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Le contenu `text` est obligatoire. Une valeur vide ne produit aucun balisage.
La taille d’image est définie directement dans le template selon le projet et
n’est pas exposée dans ACF.

Le portrait de démonstration mesure `320 × 320 px`. Forge l’importe dans la
médiathèque, puis WordPress produit le recadrage `40_40` utilisé au rendu.

## Champs ACF

Layout flexible content :

```text
acf_fc_layout = quote
```

| Libellé | Identifiant | Type ACF | Format |
| --- | --- | --- | --- |
| Citation | `text` | WYSIWYG léger | HTML éditorial |
| Image | `image` | Image | ID de média |
| Auteur | `author` | Texte | Texte simple |
| Source | `source` | Texte | Texte simple |
| URL de la source | `url` | URL | URL complète |
| Options | `options` | Groupe cloné | Options communes des strates |

## Assets et dépendances

La strate n’a pas d’asset propre. Elle utilise :

- `options()` ;
- `component('quote', [...])`.
