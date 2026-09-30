# Strate `pushes-auto`

Remonte automatiquement les derniers contenus publiés d’un type donné.
Lire d’abord le [README général des strates](../README.md).

```php
strate('pushes-auto', [
    'title' => 'Dernières actualités',
    'post_type' => 'news',
    'limit' => 3,
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte facultatif |
| `link` | `array` | `[]` | Lien ACF interne ou externe |
| `post_type` | `string` | `''` | Type enregistré à interroger |
| `card` | `string` | Card associée au type | Remplacement facultatif en PHP |
| `limit` | `int` | `3` | Nombre de contenus, borné de 1 à 12 |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Dans le BO, le layout flexible `pushes-auto` propose le type de contenu
(limité aux types avec card), le nombre de contenus, les champs de présentation
et les options communes. Il n’y a pas de sélection manuelle.

La requête récupère les publications récentes (`date DESC`), conserve les
filtres multilingues et précharge les posts et leurs métadonnées. Les cards
utilisent `theme_post_card_item` et donc les variantes ACF `card` facultatives.
Sans introduction ni résultat valide, la strate ne produit aucun balisage.

## Dépendances

La strate utilise `component('header')`, `component('list')` et la card
associée au type choisi. Son SCSS est co-localisé avec ce README.
