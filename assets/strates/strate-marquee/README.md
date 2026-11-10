# Strate `marquee`

Affiche un défilement continu de cards ou d’images. Le starter utilise par
défaut le mode images avec trois visuels de démonstration.

Lire d’abord le [README général des strates](../README.md) et le
[README du composant marquee](../../components/marquee/README.md).

## Appel

Cards :

```php
strate('marquee', [
    'title' => 'À découvrir',
    'content_type' => 'cards',
    'post_type' => 'news',
    'items' => [123, 456],
]);
```

Images :

```php
strate('marquee', [
    'content_type' => 'images',
    'images' => [
        ['image' => 123],
        ['image' => 456],
    ],
]);
```

Le mode HTML reste disponible lors d’un appel PHP :

```php
strate('marquee', [
    'content_type' => 'html',
    'items' => ['<strong>Design</strong>', '<span>Digital</span>'],
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien interne ou externe facultatif |
| `content_type` | `'html'\|'cards'\|'images'` | `'images'` | Nature des items |
| `post_type` | `string` | `''` | CPT en mode cards |
| `card` | `string` | Valeur de `post_type` | Card utilisée lors d’un appel PHP |
| `items` | `list<int\|array\|scalar\|Stringable>` | `[]` | Cards ou HTML direct |
| `images` | `list<array{image?: ID}>` | `[]` | Images ACF |
| `marquee_options` | `array` | `[]` | Réglages transmis au marquee |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Une strate sans card valide, image exploitable ou contenu HTML ne produit aucun
balisage.

## Champs ACF

```text
acf_fc_layout = marquee
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
| Vitesse | `marquee_options.speed` | Nombre | — |
| Direction | `marquee_options.direction` | Groupe de boutons | — |
| Accélération au scroll | `marquee_options.scroll_acceleration` | Vrai/faux | — |
| Suit le sens du scroll | `marquee_options.follow_scroll_direction` | Vrai/faux | — |
| Pause au survol | `marquee_options.pause_on_hover` | Vrai/faux | — |
| Pause au focus | `marquee_options.pause_on_focus` | Vrai/faux | — |
| Options | `options` | Groupe cloné | — |

ACF propose automatiquement les CPT enregistrés qui possèdent une card de même
nom. Dans le starter, le choix par défaut est `news`.

Les posts sélectionnés sont préchargés en lots avant leur validation par CPT.
Les images sont ensuite préchargées par le composant `marquee`. Ce comportement
s'applique à toutes les pages, archives et templates utilisant la strate.

## Images

La taille WordPress est définie au début du template :

```php
$image_size = 'full';
```

Les images utilisent `picture` avec `fit => 'cover'`. Le starter leur applique
un ratio `3 / 2` et une largeur maximale de `48rem`. Les trois placeholders de
`960 × 640 px` sont déclarés dans le manifeste puis importés par Forge. Ils sont
partagés avec Columns Content afin d’éviter les doublons dans la médiathèque.

## Hydratation

La strate possède le cycle de vie JavaScript. Elle force `hydrate => false` sur
le composant et délègue `pause`, `resume` et `destroy` à son contrôleur.
