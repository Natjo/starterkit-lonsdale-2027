# Composant `link`

Utilise le même contrat et le même rendu que `btn`, avec la classe racine
`link` et ses styles propres. Le rendu PHP est partagé dans
`template-parts/utils/action.php` ; aucun style `btn` n’est ajouté.

## Lien

```php
component('link', [
    'link' => [
        'url' => '/projet/',
        'title' => 'Découvrir le projet',
        'target' => '_blank',
    ],
    'element' => 'a',
    'classes' => 'link-1',
    'svg' => true,
]);
```

## Bouton avec l’apparence d’un lien

```php
component('link', [
    'name' => 'Ouvrir les détails',
    'element' => 'button',
    'classes' => 'link-1',
    'attributes' => ['type' => 'button'],
]);
```

## Élément visuel dans une card déjà cliquable

```php
component('link', [
    'name' => 'Découvrir',
    'element' => 'span',
    'classes' => 'link-1',
    'svg' => true,
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Libellé sans `link` |
| `link` | `array` | absent | Lien associatif `url`, `title`, `target` |
| `element` | `'button'\|'a'\|'span'` | selon le mode | `span` pour un élément neutre ; sinon `a` avec `link`, `button` sans `link` |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `svg` | `bool\|string` | `false` | SVG inline prioritaire sur `icon` |
| `icon` | `array associatif` | absent | Payload du composant `icon` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

## Règles de rendu

La présence de `link` sélectionne le mode lien : une URL sûre et un titre non
vide sont obligatoires, même pour un `span`. Un lien invalide ne retombe pas
sur `name`. Sans `link`, `name` fournit le libellé obligatoire.

`element => 'span'` fonctionne dans les deux modes. Les autres valeurs
conservent la racine par défaut : `a` avec `link`, `button` sans `link`.
Le `span` ne reçoit ni `href`, `target`, `rel`, `type`, `disabled`, ni attributs
de formulaire. Aucun rôle ARIA n’est ajouté implicitement.

Le bouton natif accepte `button`, `submit` ou `reset` dans `attributes.type`,
avec `button` par défaut. Il prend en charge `disabled`.

Les cibles `_self`, `_blank`, `_parent` et `_top` sont acceptées.
Avec `_blank`, `noopener noreferrer` est fusionné avec `attributes.rel`.
Pour un lien avec `aria-disabled="true"`, `href` et `target` sont retirés.

`svg => true` affiche une flèche inline de 24 × 24 avec la classe `.link-svg`,
`currentColor`, `aria-hidden="true"` et `focusable="false"`. `icon` est alors
ignoré. Sinon, l’icône associative est rendue via `component('icon', ...)`.

Sans `icon` associatif ni `svg` actif, le libellé est rendu directement dans
l’élément racine, sans `span` intérieur. Avec une icône ou un SVG, le libellé
est enveloppé dans un `span`. Cette règle s’applique aux racines `a`, `button`
et `span`, pour `link` comme pour `btn`.

Les libellés et attributs sont échappés à la sortie. `class`, `href`, `target`,
`rel` et `type` sont protégés. Les payloads positionnels et la forme plate
`url`/`title` sont refusés. Aucun JavaScript n’est requis.

## Dépendances

- `theme_link()` pour normaliser les liens ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/link/link.scss` pour les styles.

Voir aussi le [contrat de btn](../btn/README.md).

Les icônes de ce composant utilisent `16 × 16` par défaut. Les dimensions
`icon.width` et `icon.height` explicitement fournies restent prioritaires.
