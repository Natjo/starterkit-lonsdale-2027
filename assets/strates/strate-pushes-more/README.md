# Strate `pushes-more`

Remonte automatiquement les derniers contenus publiés d’un type et ajoute un
bouton « Voir plus ». Le bouton charge le lot suivant en AJAX ; son URL reste
utilisable sans JavaScript. Lire le [README général](../README.md).

```php
strate('pushes-more', [
    'title' => 'Actualités',
    'post_type' => 'news',
    'limit' => 3,
    'more_label' => 'Voir plus',
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien facultatif |
| `post_type` | `string` | `''` | Type enregistré avec card |
| `card` | `string` | Card du type | Remplacement facultatif en PHP |
| `limit` | `int` | `3` | Taille du lot, de 1 à 12 |
| `more_label` | `string` | `Voir plus` | Libellé du bouton |
| `options` | `array` | `[]` | Options communes |

Le layout ACF `pushes-more` expose ces champs, sans sélection manuelle. Chaque
instance utilise un paramètre d’URL propre (`pushes_more_N`) afin que plusieurs
strates puissent cohabiter sur la même page. Une visite directe de l’URL
affiche le lot demandé ; le module JS récupère cette page et ajoute ses cards
au lot déjà visible. Le lien disparaît après le dernier lot.

La requête et la préparation des cards sont partagées avec `pushes-auto` via
`theme_pushes_items()`. Les variantes ACF `card` et les champs métier sont
appliqués par `theme_post_card_item()`.

## Dépendances

La strate utilise `component('header')`, `component('list')`, la card du type
choisi et son module JS co-localisé. Le module est hydraté à l’approche du
viewport et annule ses requêtes à la destruction.
