# Composant `filters`

Rend un formulaire GET générique de filtres. Il ne lit aucune donnée WordPress
ou ACF et ne modifie aucune requête : l’appelant fournit les contrôles, leurs
options et leurs valeurs actives. Son mode AJAX est une amélioration progressive :
le formulaire GET reste entièrement fonctionnel sans JavaScript.

Pour une page WordPress filtrée, utiliser l’API `Theme_Listing`. Elle construit
la requête, coordonne les formulaires et rend le composant. L’appel direct à
`component('filters', [...])` est réservé aux formulaires autonomes ou aux
intégrations bas niveau dont le contexte est déjà construit.

## Utilisation recommandée avec `Theme_Listing`

```php
$filters = new Theme_Listing([
    'post_type' => 'project',
    'taxonomies' => true,
    'taxonomy_relation' => 'AND',
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'results_target' => 'project-results',
    'pretty_urls' => true,
    'search' => [
        'label' => 'Rechercher',
        'placeholder' => 'Rechercher un projet',
        'acf_fields' => ['hero-page_title'],
    ],
    'sort' => [
        'default' => 'date_desc',
        'options' => [
            'date_desc' => ['label' => 'Plus récents', 'orderby' => 'date', 'order' => 'DESC'],
            'title_asc' => ['label' => 'Titre A–Z', 'orderby' => 'title', 'order' => 'ASC'],
        ],
    ],
]);
```

Par défaut, la recherche utilise la recherche native WordPress. Lorsque
`search.acf_fields` contient un ou plusieurs noms de champs, le même champ de
recherche cible ces métadonnées ACF avec une comparaison `LIKE`. Cela ne crée
aucun contrôle supplémentaire. Les champs `acf_filters` restent réservés aux
filtres indépendants affichés par `filters()`.

La classe crée par défaut les groupes `search`, `facets` et `sort`. Chaque
méthode affiche directement le composant et peut donc être placée librement :

```php
<div class="filters-search-bar">
    <?php $filters->search(); ?>
</div>

<aside class="main-sidebar">
    <?php $filters->filters(); ?>
</aside>

<div class="filters-toolbar">
    <?php $filters->sort(); ?>
</div>

<div id="<?= esc_attr($filters->target()) ?>" aria-live="polite">
    <?php $filters->results_message(); ?>
    <?php if ($filters->has_results()) : ?>
        <?php component('list', [
            'items' => $filters->items(),
            'card' => $filters->card(),
        ]); ?>
    <?php endif; ?>
</div>
```

`results_message()` ne rend rien tant qu’aucune recherche, facette ou option de
tri non initiale n’est active. Après filtrage, elle affiche automatiquement le
compteur avec les formes zéro, singulier et pluriel. Elle accepte aussi des
classes et des messages traduits.
Le placeholder `{count}` produit un nombre dans un élément `<strong>` :

```php
<?php $filters->results_message([
    'classes' => 'search-results-message',
    'messages' => [
        'zero' => 'No results match your search.',
        'singular' => '{count} result matches your search.',
        'plural' => '{count} results match your search.',
    ],
]); ?>
```

Les chaînes peuvent être fournies par WPML ou par le mécanisme de traduction
du site. Elles sont échappées par la méthode ; le HTML arbitraire n’est pas
interprété.

Les méthodes de rendu acceptent des classes supplémentaires sans modifier la
configuration ni la requête du filtre :

```php
<?php $filters->search(['classes' => 'filters-search-wide']); ?>
<?php $filters->filters(['classes' => ['filters-sidebar', 'theme-dark']]); ?>
<?php $filters->sort(['classes' => 'filters-sort-compact']); ?>
```

Les classes structurelles générées par `Theme_Listing`, comme
`filters-search`, sont toujours conservées.

`facets()` reste disponible comme alias rétrocompatible de `filters()`.

`Theme_Listing` expose également `archive_title()`, `current_page()`,
`total_pages()`, `post_type_label()`, `context()` et `form($name)`. La classe ne
rend pas les cards ni la pagination : le template garde le contrôle de leur
HTML et de leur emplacement.

`results_target` définit l’identifiant de la zone retourné par `target()`, avec
ou sans AJAX. Lorsque l’AJAX est actif, cette même cible est transmise aux
formulaires. `ajax_target` reste accepté comme alias rétrocompatible.

Lorsque `post_type` n’est pas fourni, `Theme_Listing` détecte automatiquement
le type d’une archive native. Sur une page WordPress, `post_type_meta` permet de
lire explicitement le type dans une méta, par exemple `hub_post_type`. La
façade expose ce contexte avec `is_archive()`, `post_type()` et
`post_type_label()`.

La page courante est déterminée depuis les query vars WordPress `paged` et
`page`. L’option `page_name` permet d’utiliser un paramètre dédié sur une page
WordPress, par exemple `hub_page`, afin d’éviter sa redirection canonique vers
une fausse sous-page. `pagination_base()` fournit alors directement la base à
transmettre au composant `pagination`.

L’option `pretty_urls` place les taxonomies et le tri dans un chemin lisible :

```text
/projects/filters/project_type/application/filter_sort/date_desc/
```

Le segment réservé `filters` évite les conflits avec les pages enfant. La
recherche textuelle et les filtres ACF restent dans la query string. Le format
GET classique reste accepté sans JavaScript. Après l’ajout de la règle, les
permalinks WordPress doivent être régénérés une fois.

Avec ce mode, transmettre `pagination_base()` au composant de pagination :

```php
component('pagination', [
    'total' => $filters->total_pages(),
    'current' => $filters->current_page(),
    'base' => $filters->pagination_base(),
]);
```

L’action des formulaires utilise l’option `action` lorsqu’elle est fournie,
puis l’URL de l’archive native du type de contenu. Si cette archive est
désactivée, elle utilise automatiquement le permalien de la page WordPress
courante. `reset_url` suit la même URL par défaut. Une page comme `/projects/`
peut ainsi filtrer un CPT dont les singles partagent ce préfixe sans activer
son archive native.

## Appel direct du composant

```php
component('filters', [
    'action' => get_post_type_archive_link('project'),
    'items' => [
        [
            'type' => 'search',
            'name' => 'filter_search',
            'label' => 'Rechercher',
            'value' => 'identité',
        ],
        [
            'type' => 'taxonomy',
            'name' => 'project_type',
            'label' => 'Types de projets',
            'value' => ['website'],
            'options' => [
                ['value' => 'website', 'label' => 'Website'],
                ['value' => 'branding', 'label' => 'Branding'],
            ],
        ],
        [
            'type' => 'sort',
            'name' => 'filter_sort',
            'label' => 'Trier par',
            'value' => 'date_desc',
            'options' => [
                ['value' => 'date_desc', 'label' => 'Plus récents'],
                ['value' => 'title_asc', 'label' => 'Titre A–Z'],
            ],
        ],
    ],
    'submit_label' => 'Filtrer',
    'reset_label' => 'Réinitialiser',
    'reset_url' => get_post_type_archive_link('project'),
    'show_reset' => true,
    'result_count' => 12,
    'result_count_label' => 'projets',
    'aria_label' => 'Filtrer les projets',
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'ajax_target' => 'project-results',
]);
```

La cible est un élément existant de la page dont l’identifiant correspond à
`ajax_target` :

```php
<div id="project-results" aria-live="polite">
    <!-- liste et pagination -->
</div>
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `action` | `scalar\|Stringable` | accueil | URL cible du formulaire GET |
| `items` | `list<array>` | `[]` | Contrôles à rendre ; une liste vide ne produit aucun HTML |
| `submit_label` | `scalar\|Stringable` | `Filtrer` | Libellé du bouton de validation |
| `reset_label` | `scalar\|Stringable` | `Réinitialiser` | Libellé du lien de remise à zéro |
| `reset_url` | `scalar\|Stringable` | `''` | URL sans filtres |
| `show_reset` | `bool\|string` | `false` | Affiche le lien si son URL est valide |
| `result_count` | `int\|numeric-string\|null` | `null` | Compteur facultatif annoncé aux technologies d’assistance |
| `result_count_label` | `scalar\|Stringable` | `''` | Libellé placé après le compteur |
| `aria_label` | `scalar\|Stringable` | `''` | Nom accessible du formulaire |
| `ajax` | `bool\|string` | `false` | Active le rafraîchissement dynamique lorsque le groupe et la cible sont valides |
| `ajax_group` | `scalar\|Stringable` | `''` | Identifiant partagé par les formulaires qui pilotent les mêmes résultats |
| `ajax_target` | `scalar\|Stringable` | `''` | Identifiant HTML, sans `#`, de la zone remplacée |
| `ajax_submit_on_change` | `bool\|string` | `false` | Soumet le groupe au changement d’un contrôle, utile pour le tri |
| `pretty_urls` | `bool\|string` | `false` | Convertit en segments les paramètres listés dans `pretty_params` |
| `pretty_base_url` | `scalar\|Stringable` | `''` | Base absolue terminée par le segment des filtres |
| `pretty_params` | `list<string>` | `[]` | Paramètres autorisés dans le chemin |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs libres du formulaire |

## Mode AJAX et formulaires séparés

Plusieurs instances peuvent utiliser le même `ajax_group`. Avant une requête,
le module fusionne les valeurs de tous les formulaires du groupe, remplace la
zone `ajax_target`, actualise les formulaires depuis la réponse et met à jour
l’URL avec `history.pushState`. La pagination, la réinitialisation et les
boutons précédent/suivant du navigateur utilisent le même mécanisme.

Le serveur doit renvoyer la page HTML complète contenant une cible portant le
même identifiant et les mêmes formulaires groupés. En cas d’erreur réseau ou de
markup incomplet, le module revient à une navigation classique vers l’URL.

## Contrôles

Chaque item exige un `name` HTML valide. Les clés communes sont `type`, `name`,
`label`, `value`, `options`, `placeholder`, `autocomplete`, `classes` et
`attributes`.

| `type` | Rendu |
| --- | --- |
| `search`, `text`, `number`, `date` | Champ natif correspondant |
| `select`, `sort` | Liste déroulante |
| `checkbox` | Case unique |
| `checkboxes`, `taxonomy`, `tags`, `relation` | Groupe de cases à cocher |
| `radios` | Groupe de boutons radio |
| `boolean`, `featured` | Case unique |
| `date_range` | Champs `name[from]` et `name[to]` |
| `number_range` | Champs `name[min]` et `name[max]` |
| `hidden` | Champ caché |
| `acf`, `location`, `status`, `post_type` | Contrôle défini par `control` |

Les alias acceptent `control` avec `text`, `search`, `select`, `checkbox`,
`checkboxes`, `radios`, `number`, `date`, `date_range` ou `number_range`. Les
intervalles acceptent `from_label`, `to_label`, `min` et `max`.

Pour un contrôle multiple, `value` contient la liste des valeurs actives. Les
options suivent le contrat de `component('field')` avec `value` et `label`.

Les taxonomies hiérarchiques sont prises en charge par les mêmes options :
l’appelant reste responsable de leur ordre et de la présentation de leurs
libellés. Lorsque le composant est alimenté par `Theme_Listing` ou
`theme_build_filters_context()`, les filtres ACF configurés dans
`acf_filters` sont traduits en `meta_query` par le helper.

## Dépendances

- `component('field', [...])` ;
- `component('btn', [...])` ;
- `Theme_Listing` pour l’utilisation dans une page WordPress ;
- `assets/components/filters/filters.js` lorsque `ajax` est actif.
