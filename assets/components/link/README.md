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
    'svg' => true,
]);
```

## Bouton avec l’apparence d’un lien

```php
component('link', [
    'name' => 'Ouvrir les détails',
    'element' => 'button',
    'attributes' => ['type' => 'button'],
]);
```

## Élément visuel dans une card déjà cliquable

```php
component('link', [
    'name' => 'Découvrir',
    'element' => 'span',
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
vide sont attendus, même pour un `span`. Un titre vide est accepté uniquement
avec une icône/SVG et un `aria-label` non vide. Un lien invalide ne retombe pas
sur `name`. Sans `link`, `name` fournit le libellé visible ; son absence suit
les mêmes règles pour une icône seule.

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
non vide est enveloppé dans un `span`. Sans texte visible, aucun `span` intérieur n’est rendu. Cette règle s’applique aux racines `a`, `button`
et `span`, pour `link` comme pour `btn`.

Les libellés et attributs sont échappés à la sortie. `class`, `href`, `target`,
`rel` et `type` sont protégés. Les payloads positionnels et la forme plate
`url`/`title` sont refusés. Aucun JavaScript n’est requis.

## Icône seule

Sans `name` (ou sans `link.title` en mode lien), une icône ou `svg => true`
peut être rendue seule, sans `span` intérieur. `attributes['aria-label']`
est alors obligatoire et doit décrire l’action. Le label de l’icône seule
ne remplace pas cet attribut sur le bouton ou le lien. Sans icône/SVG,
un `aria-label` seul ne produit aucun HTML.

```php
component('link', [
    'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16],
    'classes' => 'link-icon',
    'attributes' => ['aria-label' => 'Ouvrir les détails'],
]);

// Lien avec une icône seule.
component('link', [
    'link' => ['url' => '/projet/'],
    'icon' => ['name' => 'blank'],
    'attributes' => ['aria-label' => 'Découvrir le projet'],
]);
```

La classe `link-icon` est une classe fournie par l’appelant ; elle n’active
pas le mode icône seule et n’ajoute aucun style automatiquement.

## Position de l’icône

La classe `icon-first` place l’icône ou la flèche SVG avant le texte. Sans cette
classe, elle reste après. L’ordre du HTML reste inchangé.

```php
component('link', [
    'link' => ['url' => '/contact/', 'title' => 'Contact'],
    'icon' => ['name' => 'blank'],
    'classes' => 'icon-first',
]);
```

## Couleurs contextuelles

Le composant définit `--link-color` et `--link-color-hover` pour sa couleur
normale et sa couleur au survol. Le lien standard utilise `--color-2`.
Les classes `.bg-color-*` ne définissent aucune variable propre aux liens.

| Niveau | Variables | Rôle |
| --- | --- | --- |
| Palette globale | `--color-*` | Couleurs de référence du site |
| Contexte de fond | `--bg-color`, `--bg-text` | Fond et texte du contexte courant |
| Composant | `--link-color`, `--link-color-hover` | Couleurs normale et au survol |

Avec `.link-invert`, `--link-color` reprend directement `--bg-text` du
contexte et la couleur de survol suit cette même valeur.
`--bg-text` est défini par défaut sur `:root` et redéfini par chaque
`.bg-color-*`. Cela fonctionne aussi avec des fonds imbriqués, sans modifier
`--color-text` ni nécessiter de sélecteur parent spécifique.

```php
component('link', [
    'link' => ['url' => '/contact/', 'title' => 'Contact'],
    'classes' => 'link-invert',
]);
```

La classe `link` est ajoutée automatiquement ; `link-1` n’est plus utilisée.
Le soulignement apparaît au survol. Le focus clavier conserve un contour et un
soulignement visibles avec `currentColor`.

## Dépendances

- `theme_link()` pour normaliser les liens ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/link/link.scss` pour les styles.

Voir aussi le [contrat de btn](../btn/README.md).

Les icônes de ce composant utilisent `16 × 16` par défaut. Les dimensions
`icon.width` et `icon.height` explicitement fournies restent prioritaires.
