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

Chaque résultat utilise `theme_post_card_item()`, comme le hub : groupe ACF
`card`, mapping métier déclaré par le CPT, puis replis WordPress. Pour News,
la description et les images desktop/mobile viennent de **Card - news**.
Aucun mapping spécifique au hero n’est effectué. Les contenus non publics
ou protégés et les payloads sans titre exploitable sont exclus.

## Rendu

Les payloads normalisés sont transmis à :

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
