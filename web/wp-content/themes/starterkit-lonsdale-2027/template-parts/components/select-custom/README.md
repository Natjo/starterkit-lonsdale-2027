# Composant `select-custom`

Améliore progressivement un `<select>` natif avec une interface personnalisée.
Le contrôle natif reste la source de vérité pour la soumission, la validation
et le reset du formulaire. Il reste utilisable tant que le JavaScript n’est pas
chargé.

Utiliser le composant `select` lorsque l’apparence native suffit.

## Utilisation

```php
component('select-custom', [
    'name' => 'city',
    'label' => 'Ville',
    'placeholder' => 'Choisir une ville',
    'value' => 'lyon',
    'required' => true,
    'options' => [
        ['value' => 'paris', 'label' => 'Paris'],
        ['value' => 'lyon', 'label' => 'Lyon'],
        ['value' => 'nantes', 'label' => 'Nantes', 'disabled' => true],
    ],
]);
```

Seule cette API est supportée. Les arguments
positionnels ne sont pas acceptés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `options` | `array` | `[]` | Options disponibles, obligatoire |
| `name` | `scalar\|Stringable` | `''` | Nom soumis, obligatoire |
| `id` | `scalar\|Stringable` | auto | Identifiant explicite facultatif |
| `label` | `scalar\|Stringable` | `''` | Libellé visible |
| `aria_label` | `scalar\|Stringable` | `name` | Libellé accessible si `label` est vide |
| `value` | `scalar\|array\|Stringable\|null` | `null` | Valeur ou valeurs initiales |
| `placeholder` | `scalar\|Stringable` | `Sélectionner` | Texte affiché sans sélection |
| `multiple` | `bool\|string` | `false` | Active la sélection multiple |
| `required` | `bool\|string` | `false` | Rend la sélection obligatoire |
| `disabled` | `bool\|string` | `false` | Désactive le contrôle |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs supplémentaires du conteneur |
| `select_attributes` | `array` | `[]` | Attributs supplémentaires du `<select>` |

Les chaînes `"true"` et `"false"` sont normalisées pour les booléens.

Le composant n’appelle aucune fonction de traduction. Si le placeholder doit
être traduit, transmettre directement la chaîne déjà traduite depuis l’appel.

## Options

La forme structurée demande obligatoirement `value` et `label` :

```php
'options' => [
    [
        'value' => 'design',
        'label' => 'Design',
        'selected' => true,
        'disabled' => false,
    ],
],
```

L’ancien alias `name` de `label` n’est pas accepté. La forme courte associative
reste disponible :

```php
'options' => [
    'design' => 'Design',
    'code' => 'Code',
],
```

Une option dont la valeur ou le libellé est vide est ignorée. Une option
désactivée ne peut pas être sélectionnée initialement.

En mode simple, `value` gagne sur le premier `selected => true`. Sans sélection,
le placeholder est utilisé, ou la première option active si le placeholder est
vide. Une seule option peut être sélectionnée.

En mode multiple, un tableau `value` définit la sélection exacte. Sans clé
`value`, les options portant `selected => true` sont conservées.

## Sélection multiple

```php
component('select-custom', [
    'name' => 'topics',
    'multiple' => true,
    'value' => ['design', 'code'],
    'options' => [
        'design' => 'Design',
        'code' => 'Code',
    ],
]);
```

Le suffixe `[]` est ajouté automatiquement à `name` s’il est absent. Le
formulaire produit donc `$_POST['topics'] === ['design', 'code']`. Les valeurs
contenant une virgule restent intactes.

## Clavier et cycle de vie

- `Entrée` ou `Espace` ouvre la liste et valide ou bascule l’option active.
- `Flèche haut/bas`, `Début` et `Fin` déplacent l’option active.
- La saisie de caractères recherche une option par son libellé.
- `Échap` ferme la liste.
- `Tab` ferme la liste sans bloquer la navigation native.

En mode multiple, la liste reste ouverte après chaque sélection.

Le module est hydraté à proximité du viewport. Il synchronise les événements
`input` et `change`, le reset du formulaire et la validation native. Son
nettoyage retire les écouteurs, annule les tâches différées et restaure le
`<select>` natif.
