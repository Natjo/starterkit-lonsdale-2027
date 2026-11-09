# Composant `btn`

Génère un `<button>`, un lien `<a>` ou un élément neutre `<span>` avec la
classe `btn`. Le composant fonctionne uniquement avec `component('btn', [...])`
et un payload associatif.

Le rendu PHP est partagé avec `link` dans `template-parts/utils/action.php`.
Chaque composant conserve sa classe racine, sa classe SVG et ses styles.

## Bouton

```php
component('btn', [
    'name' => 'Envoyer',
    'classes' => 'btn-1',
    'attributes' => [
        'type' => 'submit',
        'aria-label' => 'Envoyer le formulaire',
    ],
]);
```

## Lien stylé comme un bouton

```php
component('btn', [
    'link' => [
        'url' => '/projet/',
        'title' => 'Découvrir le projet',
        'target' => '',
    ],
    'classes' => 'btn-1',
    'icon' => [
        'name' => 'blank',
        'width' => 16,
        'height' => 16,
    ],
]);
```

Les arguments positionnels, les listes directes, les
enveloppes `button`/`cta` et la forme plate `url`/`title` ne sont plus
supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `name` | `scalar\|Stringable` | `''` | Libellé du bouton natif |
| `link` | `array` | absent | Données du lien `url`, `title`, `target` |
| `element` | `'button'\|'a'\|'span'` | selon le mode | `span` pour un élément neutre ; sinon `a` avec `link`, `button` sans `link` |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `svg` | `bool\|string` | `false` | Affiche la flèche SVG inline du bouton à la place de `icon` |
| `icon` | `array associatif` | absent | Payload de `component('icon', [...])` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

La présence de `link` sélectionne toujours le mode lien. Un `link` incomplet
ou invalide ne produit aucun HTML et ne retombe pas sur `name`.

## Élément racine

La présence de `link` génère un `<a>` par défaut. Sans `link`, le composant
génère un `<button>` par défaut, ou un `<span>` avec `element => 'span'`.

Exemples avec `element` explicite :

```php
// Bouton natif : <button type="button" class="btn btn-1">…</button>
component('btn', [
    'name' => 'Ouvrir',
    'element' => 'button',
    'classes' => 'btn-1',
]);

// Lien : <a href="/projet/" class="btn btn-1">…</a>
component('btn', [
    'link' => [
        'url' => '/projet/',
        'title' => 'Découvrir le projet',
    ],
    'element' => 'a',
    'classes' => 'btn-1',
]);

// Élément visuel : <span class="btn btn-1">…</span>
component('btn', [
    'name' => 'Découvrir',
    'element' => 'span',
    'svg' => true,
    'classes' => 'btn-1',
]);
```

`element` ne change pas le mode choisi par la présence de `link` : `a` exige
un lien valide et `button` s’utilise sans `link`. Seul `span` remplace la balise
dans les deux modes.

Une card dont le lien enveloppe déjà tout le contenu peut demander un élément
visuel non interactif afin d'éviter d'imbriquer deux liens :

```php
component('btn', [
    'link' => [
        'url' => '/projet/',
        'title' => 'Découvrir le projet',
    ],
    'element' => 'span',
    'classes' => 'btn-1',
]);
```

Avec `link`, le `span` utilise `link.title`. Sans `link`, il utilise `name`. Il ne rend jamais `href`, `target`, `rel`,
`type`, `disabled` ni les attributs de formulaire. Il reste volontairement non
interactif et ne reçoit aucun rôle ARIA implicite.

Toute valeur autre que `span` conserve la balise par défaut du mode.
Un `link` fourni doit rester valide, même avec `element => 'span'`.

Sans lien, pour un CTA visuel dans une card déjà cliquable :

```php
component('btn', [
    'name' => 'See',
    'element' => 'span',
    'classes' => 'btn-1',
]);
```

## Bouton natif

Le type par défaut est `button`. Seules les valeurs `button`, `submit` et
`reset` sont acceptées dans `attributes['type']`; toute autre valeur revient à
`button`.

```php
component('btn', [
    'name' => 'Indisponible',
    'attributes' => ['disabled' => true],
]);
```

## Lien, sécurité et état désactivé

Les cibles `_self`, `_blank`, `_parent` et `_top` sont acceptées. Une cible
`_blank` reçoit toujours `noopener noreferrer`. Un `rel` fourni dans
`attributes` est conservé et fusionné.

```php
component('btn', [
    'link' => [
        'url' => 'https://example.com',
        'title' => 'Site externe',
        'target' => '_blank',
    ],
    'attributes' => ['rel' => 'nofollow'],
]);
```

Un lien avec `aria-disabled="true"` est rendu sans `href` ni `target`.

## SVG intégré

```php
component('btn', [
    'name' => 'Découvrir',
    'svg' => true,
    'classes' => 'btn-1',
]);
```

`svg` affiche une flèche décorative de 24 × 24, définie directement dans le
template `btn.php`, avec la classe `.btn-svg` et la couleur `currentColor`.
Elle porte `aria-hidden="true"` et `focusable="false"`.
Cette option fonctionne pour les racines `button`, `a` et `span`.
Si `svg` est vrai, `icon` est ignoré. Par défaut (`false`), le rendu de `icon`
est conservé. Les booléens et leurs représentations usuelles en chaîne sont
normalisés avec `component_bool()`.

## Icône

L’icône utilise exclusivement un payload associatif :

```php
'icon' => [
    'name' => 'blank',
    'width' => 16,
    'height' => 16,
]
```

Une liste positionnelle comme `['blank', 16, 16]` est ignorée. L’icône
accompagne déjà le texte visible et reste donc décorative par défaut.

## Libellé

Sans `icon` associatif ni `svg` actif, le libellé est rendu directement dans
l’élément racine, sans `span` intérieur. Avec une icône ou un SVG, le libellé
est enveloppé dans un `span`. Cette règle s’applique aux racines `button`,
`a` et `span`, comme pour le composant `link`.

## Règles de rendu

- Un bouton sans `name` valide ne produit aucun HTML.
- Un lien exige une URL sûre et un titre non vide.
- Un `span` utilise `name` sans lien, ou `link.title` avec un lien valide.
- Le libellé visible est échappé avec `esc_html()`.
- Les classes sont nettoyées et dédupliquées.
- `class`, `href`, `target`, `rel` et `type` sont protégés.
- Aucun JavaScript n’est requis.

## Dépendances

- `theme_link()` pour normaliser et sécuriser les liens ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/btn/btn.scss` pour les styles.

Les icônes de ce composant utilisent `16 × 16` par défaut. Les dimensions
`icon.width` et `icon.height` explicitement fournies restent prioritaires.
