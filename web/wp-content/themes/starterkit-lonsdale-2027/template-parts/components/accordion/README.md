# Composant `accordion`

Génère une liste de panneaux repliables accessibles. Le contenu riche est
filtré par WordPress et l'ouverture peut être simple ou multiple.

Le composant fonctionne uniquement avec `component('accordion', [...])` et
un payload associatif.

## Utilisation

```php
component('accordion', [
    'items' => [
        [
            'title' => 'Comment se déroule une collaboration ?',
            'text' => '<p>Chaque mission commence par une phase de cadrage.</p>',
        ],
        [
            'title' => 'Quels sont les délais habituels ?',
            'text' => '<p>Un calendrier est partagé au lancement.</p>',
        ],
    ],
    'multiple' => false,
    'classes' => 'faq',
    'attributes' => ['id' => 'faq-homepage'],
]);
```

Les listes directes et les arguments positionnels ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `list<array>` | `[]` | Panneaux de l'accordéon |
| `multiple` | `bool` | `false` | Autorise plusieurs panneaux ouverts |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs du conteneur |

Chaque item utilise strictement :

| Clé | Type | Rôle |
| --- | --- | --- |
| `title` | `scalar\|Stringable` | Texte du bouton |
| `text` | `scalar\|Stringable` | Contenu riche du panneau |

Un item sans titre ou sans texte valide est ignoré. Si aucun item ne reste,
le composant ne produit aucun HTML.

## Contrat ACF

Le layout flexible contient uniquement le repeater `items` :

```text
accordion
└── items
    ├── title (text)
    └── text (wysiwyg)
```

`multiple` reste une décision du template et ne doit pas être un champ ACF :

```php
component('accordion', array_replace($args, [
    'multiple' => false,
]));
```

## Modes d'ouverture

Avec `multiple => false` :

- le premier panneau est ouvert au chargement ;
- ouvrir un panneau ferme les autres ;
- le panneau actif peut être refermé.

Avec `multiple => true` :

- tous les panneaux sont ouverts au chargement ;
- chaque panneau évolue indépendamment.

Les chaînes `"true"` et `"false"` sont correctement normalisées.

## Accessibilité

- chaque déclencheur est un véritable `<button>` ;
- `aria-expanded` reflète son état ;
- `aria-controls` référence le panneau ;
- `aria-hidden` reflète la visibilité du panneau ;
- `aria-labelledby` donne au panneau le nom du bouton ;
- chaque panneau utilise `role="region"` ;
- les identifiants sont uniques grâce à `wp_unique_id()` ;
- le titre est échappé et le contenu passe par `wp_kses_post()`.

## JavaScript

Le module `components/accordion` :

- est hydraté à proximité de la zone visible ;
- sélectionne uniquement les `.details` enfants directs ;
- vérifie la relation `aria-controls`/`id` ;
- synchronise `aria-expanded` et `aria-hidden` ;
- nettoie ses événements avec `AbortController`.

L'animation repose sur le CSS et respecte `prefers-reduced-motion`.

## Attributs protégés

Les attributs suivants sont contrôlés par le composant :

- `class` ;
- `data-module` ;
- `data-context` ;
- `data-multiple`.

Les autres attributs sûrs passent par `component_attributes()`.

## Dépendances

- `component_classes()` ;
- `component_attributes()` ;
- `wp_unique_id()` ;
- `assets/components/accordion/accordion.js` ;
- `assets/components/accordion/accordion.scss`.
