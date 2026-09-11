# Strate `slider`

Affiche un slider de cards ou d’images. Le starter utilise le mode
cards avec le CPT `news`.

Lire d’abord le [README général des strates](../README.md) et le
[README du composant slider](../../components/slider/README.md).

## Appel

Cards :

```php
strate('slider', [
    'title' => 'À la une',
    'content_type' => 'cards',
    'post_type' => 'news',
    'items' => [123, 456],
]);
```

Images :

```php
strate('slider', [
    'content_type' => 'images',
    'images' => [
        ['image' => 123],
        ['image' => 456],
    ],
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien interne ou externe facultatif |
| `content_type` | `'cards'\|'images'` | `'cards'` | Nature des slides |
| `post_type` | `string` | `''` | CPT des items en mode cards |
| `card` | `string` | Valeur de `post_type` | Card utilisée lors d’un appel PHP |
| `items` | `list<int\|array>` | `[]` | IDs ACF ou payloads de card lors d’un appel PHP |
| `images` | `list<array{image?: ID}>` | `[]` | Images du slider |
| `slider_options` | `array` | `[]` | Options transmises au slider |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Une strate sans card valide ni image exploitable ne produit aucun balisage.

## Champs ACF

```text
acf_fc_layout = slider
```

| Libellé | Identifiant | Type ACF | Condition |
| --- | --- | --- | --- |
| Titre | `title` | WYSIWYG léger | — |
| Texte | `text` | WYSIWYG | — |
| Lien | `link` | Lien | — |
| Type de contenu | `content_type` | Groupe de boutons | — |
| Type de publication | `post_type` | Sélection dynamique | `content_type = cards` |
| Contenus | `items` | Relation retournant des IDs | `content_type = cards` |
| Images | `images` | Répéteur | `content_type = images` |
| Image | `image` | Image retournant un ID | Sous-champ de `images` |
| Navigation | `slider_options.navigation` | Vrai/faux | — |
| Pagination | `slider_options.pagination` | Vrai/faux | — |
| Timeline | `slider_options.timeline` | Vrai/faux | — |
| Options | `options` | Groupe cloné | — |

Le choix du CPT est alimenté avec les types enregistrés possédant une card de
même nom. Dans le starter, cela sélectionne `news` et rend `card-news`.
Un appel PHP peut aussi fournir explicitement `card` et des payloads
associatifs sans dépendre d’un CPT.

Les posts sélectionnés sont préchargés en lots avant leur validation par CPT.
Les images sont ensuite préchargées par le composant `slider`. Ce comportement
s'applique quel que soit le template WordPress qui rend la strate.

La taille WordPress des images est définie directement au début du template :

```php
$image_size = 'full';
```

Elle ne constitue pas un champ ACF et doit être adaptée aux tailles enregistrées
par le projet. Les images sont transmises à `picture` avec `fit => 'cover'` ;
leur ratio reste défini dans le SCSS de la strate selon le design.

## Adaptation à un projet

Cette strate sert de base générique. Lorsque le contenu et le design du projet
sont validés, conserver uniquement le comportement utile, adapter les champs
ACF correspondants puis renommer ensemble le layout ACF, le dossier, le
template, les assets, les appels et ce README.

## Hydratation

La strate possède le cycle de vie JavaScript. Elle force `hydrate => false` sur
le composant, puis initialise et nettoie le contrôleur depuis
`assets/strates/strate-slider/strate-slider.js`.
