# Common `related`

Affiche une sélection automatique des dernières News publiées avant le
prefooter.

## Appel

```php
common('related');
```

Le common est appelé dans `single-news.php`. Il ne reçoit aucun payload et
n’utilise aucun champ ACF propre.

## Contenu

Le titre et le texte sont volontairement écrits dans le template :

```text
À découvrir
Découvrez nos dernières actualités.
```

La requête récupère au maximum trois contenus :

```php
[
    'post_type' => 'news',
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'ids',
]
```

`suppress_filters` reste désactivé afin de conserver les filtres multilingues.
Les métadonnées des résultats sont préchargées avant le rendu des cards.
Sur une page individuelle News, le contenu consulté est exclu de la requête.

Le common ne produit aucun HTML si le CPT `news` n’existe pas ou s’il ne
contient aucune publication.

Pour chaque résultat, le common transforme `hero-news.date`,
`hero-news.title` et l’ID `hero-news.images.desktop` en payload direct pour
`card-news`, puis ajoute le permalien WordPress. La card reste ainsi
indépendante de la structure ACF du CPT.

## Rendu

Les identifiants sont transmis à :

```php
component('list', [
    'items' => $items,
    'card' => 'news',
]);
```

La grille affiche une colonne sur mobile et jusqu’à trois colonnes à partir du
breakpoint `md`.

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])` ;
- `component('list', [...])` ;
- `card('news', ...)`, appelée par le composant `list` ;
- `assets/common/related/related.scss`, intégré à `app.css`.
