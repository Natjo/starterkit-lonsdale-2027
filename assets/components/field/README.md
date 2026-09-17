# Composant `field`

Rend un champ de formulaire. Il ne génère pas la balise `<form>` parente.

```php
<form class="form" data-module="components/field">
    <?php component('field', [
        'type' => 'email',
        'name' => 'email',
        'label' => 'Adresse e-mail',
        'required' => true,
        'mandatory_msg' => 'Saisissez une adresse e-mail.',
    ]); ?>
</form>
```

Seule cette API est supportée. `component('form', ...)`,
la fonction globale `form()` et les arguments positionnels ont été retirés.

## Types supportés

- `text`
- `search`
- `textarea`
- `select`
- `checkbox`
- `checkboxes`
- `radios`
- `number`
- `email`
- `url`
- `tel`
- `date`
- `password`

`radio` est normalisé en `radios`.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `type` | `string` | `'text'` | Type du champ |
| `name` | `scalar\|Stringable` | `''` | Nom du champ, obligatoire |
| `id` | `scalar\|Stringable` | dérivé de `name` | Identifiant du champ |
| `label` | `scalar\|Stringable` | `''` | Libellé visible |
| `value` | `scalar\|Stringable` | `''` | Valeur initiale |
| `required` | `bool\|string` | `false` | Champ obligatoire |
| `mandatory_msg` | `scalar\|Stringable` | `''` | Message de valeur manquante |
| `type_mismatch_msg` | `scalar\|Stringable` | `''` | Message de type, longueur ou valeur numérique invalide |
| `pattern_mismatch_msg` | `scalar\|Stringable` | `''` | Message lorsque `pattern` ne correspond pas |
| `placeholder` | `scalar\|Stringable` | `''` | Placeholder |
| `options` | `array` | `[]` | Options de select, radios ou checkboxes |
| `hint` | `scalar\|Stringable` | `''` | Aide affichée sous le champ |
| `rows` | `int` | `4` | Nombre de lignes du textarea |
| `min` / `max` | `scalar\|null` | `null` | Bornes du champ number |
| `minlength` | `int\|null` | `null` | Longueur minimale du texte |
| `pattern` | `scalar\|Stringable` | `''` | Expression de validation HTML |
| `autocomplete` | `scalar\|Stringable` | `''` | Valeur autocomplete |
| `disabled` | `bool\|string` | `false` | Désactive le champ |
| `checked` | `bool\|string` | `false` | État de la checkbox simple |
| `classes` | `string\|array` | `''` | Classes du conteneur `.field` |
| `attributes` | `array` | `[]` | Attributs du conteneur |

Les champs `select`, `radios` et `checkboxes` nécessitent des options. Le module
JavaScript est placé sur le `<form>` parent. Il active `novalidate` seulement
après son initialisation afin que la validation HTML native reste disponible
avant l’hydratation. Il assure ensuite les messages, le focus du premier champ
invalide, le reset et le nettoyage de ses écouteurs.
