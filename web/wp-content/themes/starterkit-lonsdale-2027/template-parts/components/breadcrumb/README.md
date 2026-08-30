# Composant `breadcrumb`

Affiche un fil d’Ariane accessible. La hiérarchie WordPress et le schéma SEO
sont gérés séparément par `inc/front/breadcrumb.php`.

## Appel

```php
component('breadcrumb', [
    'items' => theme_get_breadcrumb_items(),
]);
```

Le composant reste utilisable avec un parcours entièrement personnalisé :

```php
component('breadcrumb', [
    'items' => [
        ['name' => 'Accueil', 'url' => home_url('/')],
        ['name' => 'Actualités', 'url' => get_post_type_archive_link('news')],
        ['name' => 'Titre de la page'],
    ],
    'aria_label' => 'Vous êtes ici',
    'classes' => 'hero-breadcrumb',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `array` | `[]` | Éléments associatifs contenant `name` et éventuellement `url` |
| `aria_label` | `scalar\|Stringable` | `Fil d’Ariane` | Nom accessible du `<nav>` |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires du `<nav>` |

Le composant ne produit rien lorsqu’il reste moins de deux éléments valides.
Le dernier élément est toujours rendu comme la page courante, sans lien.

## Parcours WordPress

`theme_get_breadcrumb_items()` prend en charge :

- les pages et leurs parents ;
- les articles et la page des articles ;
- les CPT possédant une archive ;
- les taxonomies hiérarchiques ;
- les archives, recherches et erreurs 404.

Le parcours peut être adapté par projet :

```php
add_filter('theme_breadcrumb_items', function (array $items): array {
    // Ajouter, retirer ou renommer un niveau.
    return $items;
});
```

Les libellés génériques sont modifiables sans réécrire le resolver :

```php
add_filter('theme_breadcrumb_labels', function (array $labels): array {
    $labels['home'] = 'Home';
    $labels['404'] = 'Not found';
    return $labels;
});
```

## SEO

Le thème produit automatiquement un `BreadcrumbList` JSON-LD dans `wp_head`
à partir du même parcours. Aucun Microdata n’est ajouté au HTML.

Lorsque Yoast SEO est actif, le JSON-LD autonome du thème est désactivé pour
éviter un doublon. Le filtre `wpseo_schema_breadcrumb` aligne alors le
`BreadcrumbList` de Yoast sur les éléments retournés par
`theme_get_breadcrumb_items()`.

La génération autonome reste surchargeable :

```php
add_filter('theme_breadcrumb_schema_enabled', '__return_true');
```

Ne forcer cette option que si le `BreadcrumbList` du plugin SEO a été désactivé.
Le schéma n’est pas produit sur l’accueil ni sur une erreur 404.

## Sécurité

- les noms sont rendus avec `esc_html()` ;
- les URL sont filtrées avec `esc_url()` ;
- `class` et `aria-label` sont réservés ;
- les classes et attributs libres utilisent les helpers communs.
