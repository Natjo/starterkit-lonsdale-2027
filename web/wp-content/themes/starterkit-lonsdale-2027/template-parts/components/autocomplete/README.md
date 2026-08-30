# Composant `autocomplete`

Génère une combobox accessible avec suggestions locales et un `<select>` natif
comme fallback. Le composant fonctionne uniquement avec
`component('autocomplete', [...])` et un payload associatif.

## Utilisation

```php
component('autocomplete', [
    'name' => 'city',
    'label' => 'Ville',
    'placeholder' => 'Saisir une ville',
    'value' => 'lyon',
    'required' => true,
    'options' => [
        ['value' => 'paris', 'label' => 'Paris', 'alt' => 'Île-de-France'],
        ['value' => 'lyon', 'label' => 'Lyon', 'alt' => 'Rhône'],
        ['value' => 'nantes', 'label' => 'Nantes', 'disabled' => true],
    ],
    'classes' => 'autocomplete-city',
    'attributes' => ['data-tracking' => 'city'],
]);
```

Les arguments positionnels et les listes directes
ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `options` | `array` | `[]` | Options locales obligatoires |
| `name` | `scalar\|Stringable` | `''` | Nom obligatoire du `<select>` soumis |
| `label` | `scalar\|Stringable` | `name` | Libellé visible |
| `value` | `scalar\|Stringable` | `''` | Valeur initiale |
| `placeholder` | `scalar\|Stringable` | `''` | Texte indicatif de la combobox |
| `required` | `bool` | `false` | Rend la sélection obligatoire |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Les chaînes `"true"`, `"false"`, `"1"` et `"0"` sont normalisées pour
`required`.

## Options

Une option structurée utilise strictement :

```php
[
    'value' => 'paris',
    'label' => 'Paris',
    'alt' => 'Île-de-France',
    'disabled' => false,
    'selected' => false,
]
```

`value` et `label` sont obligatoires. L’ancien alias `name` du libellé n’est
plus interprété. `alt` fournit des termes supplémentaires pour la recherche.

Une forme associative courte reste disponible :

```php
'options' => [
    'paris' => 'Paris',
    'lyon' => 'Lyon',
],
```

Une option désactivée reste dans le fallback natif, mais n’apparaît pas dans
les suggestions. Lorsque `value` est vide, le premier `selected => true` actif
devient la valeur initiale.

## Amélioration progressive

Avant le JavaScript, le `<select>` reste visible, accessible et utilisable.
Après hydratation, la racine reçoit `is-ready`, le select est masqué
visuellement et la combobox prend le relais. `destroy()` restaure le select,
son état `required`, son label et son focus éventuel.

Le champ visible ne possède pas de `name` : seul le select est soumis. Une
saisie libre sans correspondance exacte remet la valeur soumise à vide. Chaque
changement réel émet `input`, puis `change`, avec propagation.

Le champ utilise `autocomplete="new-password"` pour empêcher les suggestions
de données personnelles du navigateur. Ce choix est volontaire : certains
navigateurs ignorent `autocomplete="off"` pour les villes, adresses ou noms.

## Recherche et clavier

La recherche locale examine `label` et `alt`, sans réseau, sans distinction de
casse ou d’accent.

- `Flèche bas` et `Flèche haut` parcourent les suggestions.
- `Entrée` sélectionne l’option active.
- `Échap`, `Tab` et un clic extérieur ferment la liste.
- Le focus DOM reste dans le champ grâce à `aria-activedescendant`.
- `aria-selected`, `aria-expanded` et une région `aria-live` exposent l’état.
- Les collages, compositions IME et saisies mobiles passent par `input`.

## Cycle de vie

Le module est hydraté à proximité du viewport. Il utilise une instance par
racine, un `AbortController` pour les événements locaux et une écoute extérieure
uniquement lorsque la liste est ouverte. Tout est nettoyé à la destruction.

Une recherche distante nécessite un autre contrat avec annulation des requêtes
et gestion des erreurs.

## Attributs protégés et dépendances

`class`, `data-module` et `data-context` sont contrôlés par le composant.

- `assets/components/autocomplete/autocomplete.js` ;
- `assets/components/autocomplete/autocomplete.scss` ;
- `component_classes()` et `component_attributes()`.
