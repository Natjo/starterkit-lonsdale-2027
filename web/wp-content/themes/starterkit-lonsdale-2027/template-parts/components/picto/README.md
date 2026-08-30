# Composant `picto`

Génère un conteneur inline `.picto` autour du composant `icon`. Il fonctionne
uniquement avec `component('picto', [...])` et un payload associatif.

## Utilisation

```php
component('picto', [
    'name' => 'star',
    'label' => 'Étoile',
    'classes' => 'picto-featured',
    'attributes' => ['data-tracking' => 'star'],
]);
```

Les arguments positionnels, les listes directes et
l’ancien alias `icon` ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Nom de l’icône, obligatoire |
| `label` | `scalar\|Stringable` | `''` | Nom accessible facultatif |
| `classes` | `string\|array` | `''` | Classes supplémentaires du conteneur |
| `attributes` | `array` | `[]` | Attributs supplémentaires du conteneur |

Un nom vide ou invalide ne produit aucun HTML. Les dimensions de l’icône sont
volontairement fixes à `24 × 24`; la présentation du pictogramme se règle avec
les classes CSS.

Sans `label`, l’icône interne est décorative. Fournir un libellé uniquement si
le pictogramme transmet une information absente du texte voisin.

## Dépendances

- `component('icon', [...])` pour le SVG ;
- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `assets/components/picto/picto.scss` pour les styles.
