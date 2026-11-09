# Strate `slider-cards`

Affiche des cards éditoriales rédigées directement dans le BO, sans relation
avec une page, un post ou un CPT. Lire le [README général](../README.md), le
[README du slider](../../components/slider/README.md) et celui de
[`card-slide`](../../cards/card-slide/README.md).

```php
strate('slider-cards', [
    'title' => 'À découvrir',
    'items' => [
        ['title' => 'Premier sujet', 'text' => '<p>Présentation.</p>', 'image' => 123],
        ['title' => 'Second sujet', 'link' => ['url' => '/sujet/', 'title' => 'Lire']],
    ],
]);
```

Le layout ACF `slider-cards` propose un répéteur `items` limité à 12 lignes.
Chaque ligne contient `title`, `text`, `image` (ID) et `link`, correspondant
au manifeste de `card-slide`. Titre, texte, lien de la strate, réglages du
slider, libellé accessible et options communes restent distincts des champs
de chaque card.

Une ligne sans titre, texte ni image est ignorée. Sans card valide, aucun
balisage n’est produit. Le composant `slider` gère sa propre hydratation JS ;
la strate n’a pas de module dédié.

Les lignes peuvent aussi être imbriquées sous `card`, pour réutiliser le groupe
ACF **Card - slide** par clonage, sans préfixage de ses noms de champs.
