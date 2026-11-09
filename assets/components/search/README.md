# Composant `search`

Génère un formulaire de recherche accessible envoyé en `GET`. Le composant
fonctionne uniquement avec `component('search', [...])` et un payload
associatif.

## Utilisation

```php
component('search', [
    'value' => get_search_query(),
    'action' => home_url('/'),
    'name' => 's',
    'label' => 'Rechercher sur le site',
    'placeholder' => 'Votre recherche',
    'submit_label' => 'Rechercher',
    'classes' => 'search-header',
    'attributes' => ['data-tracking' => 'search'],
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `value` | `scalar\|Stringable` | `''` | Recherche initiale |
| `action` | `scalar\|Stringable` | `home_url('/')` | URL du formulaire |
| `name` | `scalar\|Stringable` | `'s'` | Nom du paramètre GET |
| `label` | `scalar\|Stringable` | `'Rechercher'` | Libellé accessible du champ |
| `placeholder` | `scalar\|Stringable` | `'Rechercher…'` | Texte indicatif |
| `submit_label` | `scalar\|Stringable` | `'Rechercher'` | Texte du bouton |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `autocomplete` | `string` | `''` | Autocomplétion du champ, attribut absent par défaut |
| `hidden_fields` | `list<array{name,value}>` | `[]` | Paramètres cachés conservés lors de la recherche |
| `attributes` | `array` | `[]` | Attributs supplémentaires du formulaire |

Le composant ne contient aucune clé de traduction. L’appelant doit transmettre
les trois textes d’interface déjà traduits lorsque les valeurs par défaut ne
conviennent pas.

## Comportement

- Le formulaire utilise toujours `method="get"` et `role="search"`.
- Le champ utilise `type="search"`.
- `action` est filtré avec `esc_url()` ; une valeur invalide revient à
  `home_url('/')`.
- `name` doit commencer par une lettre et ne contenir que lettres, chiffres,
  tirets ou underscores ; sinon `s` est utilisé.
- Le label visuel est masqué avec `sr-only`, mais reste associé au champ.
- Un identifiant unique est généré avec `wp_unique_id()`.
- La valeur, le placeholder et les libellés sont échappés.
- Aucun JavaScript n’est nécessaire.

## Attributs protégés

`class`, `action`, `method` et `role` sont contrôlés par le composant. Les autres
attributs sûrs peuvent être ajoutés avec `attributes`.

## Dépendances

- `home_url()` et `wp_unique_id()` ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/search/search.scss`.

## Recherche dans un listing

`Theme_Listing::search()` réutilise ce composant pour le groupe de recherche
simple : seul le champ et le bouton sont visibles. `hidden_fields` conserve
les facettes et le tri sélectionnés en navigation GET. Les noms invalides,
valeurs complexes et doublons du nom du champ de recherche sont ignorés.
Les attributs `data-filters-*` et `data-module="components/filters"` permettent
au coordinateur des filtres d’assurer les recherches AJAX. Le composant reste
un formulaire GET autonome sans ces attributs.
