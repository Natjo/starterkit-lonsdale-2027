# Fil d’Ariane

Affiche le parcours de la page courante avec le composant `breadcrumb`.

```php
common('breadcrumb');
```

Le common ne reçoit aucun argument. Il récupère le parcours WordPress avec
`theme_get_breadcrumb_items()` : pages et parents, articles, CPT avec archive,
taxonomies, archives, recherche et 404. Il ne produit rien si le parcours
contient moins de deux éléments valides, notamment sur l’accueil.

Le dernier élément désigne la page courante et ne possède pas de lien.
Les styles et le balisage accessible sont ceux du
[composant breadcrumb](../../components/breadcrumb/README.md) ; le common
n’ajoute ni wrapper, ni CSS, ni JavaScript.

Les filtres `theme_breadcrumb_items` et `theme_breadcrumb_labels` permettent
d’adapter le parcours et les libellés. L’intégration SEO existante synchronise
le JSON-LD de Yoast avec ce parcours ; sans Yoast, le thème produit son propre
`BreadcrumbList`. Le common ne génère aucun schéma supplémentaire.

Le manifeste déclare la dépendance au composant `breadcrumb` pour Forge.
Ajouter l’appel dans les templates souhaités et déclarer `breadcrumb` dans
leurs dépendances `common`.
