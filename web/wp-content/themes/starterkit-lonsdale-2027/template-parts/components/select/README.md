# Composant `select`

Génère un `<select>` HTML natif, accessible et sans JavaScript. Le composant
fonctionne uniquement avec `component('select', [...])` et un payload
associatif.

Utiliser `select-custom` uniquement lorsqu’une interface personnalisée est
réellement nécessaire.

## Utilisation

```php
component('select', [
    'name' => 'city',
    'id' => 'billing-city',
    'label' => 'Ville',
    'placeholder' => 'Choisir une ville',
    'value' => 'lyon',
    'required' => true,
    'options' => [
        ['value' => 'paris', 'label' => 'Paris'],
        ['value' => 'lyon', 'label' => 'Lyon'],
        ['value' => 'nantes', 'label' => 'Nantes', 'disabled' => true],
    ],
    'classes' => 'select-city',
    'attributes' => ['data-field' => 'billing-city'],
    'select_attributes' => ['autocomplete' => 'address-level2'],
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `options` | `array` | `[]` | Options obligatoires |
| `name` | `scalar\|Stringable` | `''` | Nom soumis obligatoire |
| `id` | `scalar\|Stringable` | auto | Identifiant facultatif |
| `label` | `scalar\|Stringable` | `''` | Libellé visible |
| `aria_label` | `scalar\|Stringable` | `name` | Nom accessible sans label visible |
| `value` | `scalar\|Stringable\|null` | `null` | Valeur initiale prioritaire |
| `placeholder` | `scalar\|Stringable` | `''` | Première option vide |
| `required` | `bool` | `false` | Rend une sélection obligatoire |
| `disabled` | `bool` | `false` | Désactive le contrôle |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs du conteneur |
| `select_attributes` | `array` | `[]` | Attributs du `<select>` |

Les chaînes `"true"`, `"false"`, `"1"` et `"0"` sont normalisées pour les
booléens.

## Options

Une option structurée utilise strictement :

```php
[
    'value' => 'lyon',
    'label' => 'Lyon',
    'selected' => false,
    'disabled' => false,
]
```

`value` et `label` sont obligatoires. L’ancien alias `name` du libellé n’est
plus interprété. Une valeur ou un libellé vide fait ignorer l’option; la valeur
vide reste réservée au placeholder.

La forme associative courte reste disponible :

```php
'options' => [
    'paris' => 'Paris',
    'lyon' => 'Lyon',
],
```

## Priorité de sélection

Le composant conserve au maximum une option sélectionnée :

1. la première option active correspondant à `value` ;
2. sinon la première option active avec `selected => true` ;
3. sinon le placeholder lorsqu’il existe ;
4. sinon la première option active.

Une option désactivée ne peut pas devenir la sélection initiale. Avec
`required => true`, le placeholder est désactivé afin d’imposer une valeur
réelle.

## Identifiant et accessibilité

Sans `id`, `wp_unique_id()` produit un identifiant unique, y compris lorsque
plusieurs champs partagent le même `name`.

Un `label` visible est recommandé. Sans label, `aria_label` nomme le contrôle
et utilise `name` en dernier recours.

`select_attributes` accepte notamment `aria-describedby`, `aria-invalid`,
`autocomplete`, `form` et les attributs `data-*`. `id`, `name`, `class`,
`required`, `disabled`, `multiple`, `aria-label` et `aria-labelledby` sont
protégés.

Le composant ne gère volontairement pas `multiple`.

## Dépendances

- `wp_unique_id()` ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/select/select.scss`.
