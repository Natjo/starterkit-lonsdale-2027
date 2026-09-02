# Composant `icon`

Génère une icône SVG depuis le sprite du thème. Le composant fonctionne
uniquement avec `component('icon', [...])` et un payload associatif.

## Utilisation

```php
component('icon', [
    'name' => 'star',
    'width' => 24,
    'height' => 24,
    'classes' => 'icon-featured',
    'label' => 'Étoile',
    'attributes' => ['data-animation' => 'rotate'],
]);
```

Les arguments positionnels et les listes directes ne sont
plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Identifiant du symbole dans le sprite |
| `width` | `int\|numeric-string` | `24` | Largeur minimale de `1` |
| `height` | `int\|numeric-string` | `24` | Hauteur minimale de `1` |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `label` | `scalar\|Stringable` | `''` | Nom accessible facultatif |
| `attributes` | `array` | `[]` | Attributs SVG supplémentaires |

## Accessibilité

Sans `label`, l’icône est décorative :

```html
<svg aria-hidden="true" focusable="false">…</svg>
```

Avec `label`, elle devient informative :

```html
<svg role="img" aria-label="Étoile" focusable="false">…</svg>
```

Si l’icône accompagne déjà un texte visible, ne pas fournir de `label`.

## Sprite et rendu

Le symbole référencé est :

```text
THEME_ASSETS/img/icons.svg#{name}
```

- `name` est nettoyé avec `sanitize_html_class()`.
- Un nom vide ou invalide ne produit aucun SVG.
- Les dimensions invalides reprennent `24`; les valeurs inférieures à `1`
  deviennent `1`.
- Les classes `icon` et `icon-{name}` sont toujours ajoutées.
- Le composant ne lit pas le sprite et ne vérifie pas l’existence du symbole.

## Attributs protégés

`class`, `width`, `height`, `role`, `aria-label`, `aria-hidden` et `focusable`
sont contrôlés par le composant. Les autres attributs sûrs peuvent être passés
dans `attributes`.

## Dépendances

- `assets/img/icons.svg` pour le sprite ;
- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `sanitize_html_class()`, `esc_url()` et `esc_attr()` pour la sécurisation.
