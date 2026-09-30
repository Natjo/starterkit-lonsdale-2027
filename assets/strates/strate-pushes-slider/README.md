# Strate `pushes-slider`

Remonte automatiquement les derniers contenus publiés d’un type dans le
composant `slider`. Lire le [README général](../README.md) et le
[README du slider](../../components/slider/README.md).

```php
strate('pushes-slider', [
    'title' => 'À découvrir',
    'post_type' => 'news',
    'limit' => 6,
    'slider_options' => [
        'navigation' => true,
        'pagination' => true,
        'timeline' => false,
    ],
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
| `limit` | `int` | `6` | Nombre de slides, borné de 1 à 12 |
| `slider_options` | `array` | `[]` | Navigation, pagination et timeline du composant |
| `aria_label` | `string` | Titre sans HTML ou « Carrousel des contenus » | Nom accessible |
| `options` | `array` | `[]` | Options communes |

Le layout ACF `pushes-slider` expose le type de contenu, le nombre de slides,
les trois contrôles du slider et son libellé accessible. Il n’y a pas de
sélection manuelle. La requête et les payloads de cards sont partagés avec
`pushes-auto` via `theme_pushes_items()` ; le composant `slider` possède son
propre cycle de vie JavaScript.

La strate n’ajoute pas de module JS : le composant `slider` déclare et hydrate
le sien à l’approche du viewport. Son SCSS règle le nombre de cards visibles
selon la largeur de l’écran. Sans introduction ni contenu valide, elle ne
produit aucun balisage.
