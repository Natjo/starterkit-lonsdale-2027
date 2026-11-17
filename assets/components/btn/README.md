# Composant `btn`

Génère un `<button>`, un lien `<a>` ou un élément neutre `<span>` avec la
classe `btn`. Le composant fonctionne uniquement avec `component('btn', [...])`
et un payload associatif.

Le rendu PHP est partagé avec `link` dans `template-parts/utils/action.php`.
Chaque composant conserve sa classe racine, sa classe SVG et ses styles.

## Variantes visuelles

La classe `btn` est ajoutée automatiquement par le composant. Passer la variante
souhaitée dans `classes` ; aucune variante visuelle n’est sélectionnée par défaut.

| `classes` | Apparence |
| --- | --- |
| `btn-solid` | Fond plein, palette standard |
| `btn-solid btn-invert` | Fond plein adapté au contexte du fond |
| `btn-outline` | Bordure, fond transparent |
| `btn-ghost` | Sans bordure, fond transparent ; léger fond au survol |

```php
component('btn', ['name' => 'Découvrir', 'classes' => 'btn-solid']);
component('btn', ['name' => 'Découvrir', 'classes' => 'btn-solid btn-invert']);
component('btn', ['name' => 'En savoir plus', 'classes' => 'btn-outline']);
component('btn', ['name' => 'Annuler', 'classes' => 'btn-ghost']);
```

`btn-invert` est un modificateur de `btn-solid`, pas une variante autonome.
Les variantes fonctionnent avec un bouton natif, un lien ou un `span` et
acceptent les options `icon` et `svg` décrites plus bas. Les anciens noms
`btn-1`, `btn-2` et `light` sont remplacés, sans alias CSS.

## Bouton

```php
component('btn', [
    'name' => 'Envoyer',
    'classes' => 'btn-solid',
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
    'classes' => 'btn-solid',
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

La présence de `link` sélectionne toujours le mode lien. Un `link` sans URL sûre
ne produit aucun HTML et ne retombe pas sur `name`.

## Élément racine

La présence de `link` génère un `<a>` par défaut. Sans `link`, le composant
génère un `<button>` par défaut, ou un `<span>` avec `element => 'span'`.

Exemples avec `element` explicite :

```php
// Bouton natif : <button type="button" class="btn btn-solid">…</button>
component('btn', [
    'name' => 'Ouvrir',
    'element' => 'button',
    'classes' => 'btn-solid',
]);

// Lien : <a href="/projet/" class="btn btn-solid">…</a>
component('btn', [
    'link' => [
        'url' => '/projet/',
        'title' => 'Découvrir le projet',
    ],
    'element' => 'a',
    'classes' => 'btn-solid',
]);

// Élément visuel : <span class="btn btn-solid">…</span>
component('btn', [
    'name' => 'Découvrir',
    'element' => 'span',
    'svg' => true,
    'classes' => 'btn-solid',
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
    'classes' => 'btn-solid',
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
    'classes' => 'btn-solid',
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
    'classes' => 'btn-solid',
]);
```

`svg` affiche une flèche décorative de 24 × 24, définie directement dans le
rendu partagé `template-parts/utils/action.php`, avec la classe `.btn-svg`
et la couleur `currentColor`.
Elle porte `aria-hidden="true"` et `focusable="false"`.
Cette option fonctionne pour les racines `button`, `a` et `span`.
Si `svg` est vrai, `icon` est ignoré. Par défaut (`false`), le rendu de `icon`
est conservé. Les booléens et leurs représentations usuelles en chaîne sont
normalisés avec `component_bool()`.

## Icône

La classe `icon-first` place l’icône ou la flèche SVG avant le libellé.
Sans cette classe, elle reste après. Ce réglage est purement visuel et ne
modifie pas l’ordre du HTML.

```php
component('btn', [
    'name' => 'Découvrir',
    'icon' => ['name' => 'blank'],
    'classes' => 'btn-solid icon-first',
]);
```

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
non vide est enveloppé dans un `span`. Sans texte visible, aucun `span` intérieur n’est rendu. Cette règle s’applique aux racines `button`,
`a` et `span`, comme pour le composant `link`.

## Règles de rendu

- Sans `name`, un bouton exige une icône ou un SVG et un `aria-label` non vide.
- Un lien exige une URL sûre et un titre non vide, ou une icône/SVG avec `aria-label`.
- Un `span` utilise `name` sans lien, ou `link.title` avec un lien valide.
- Le libellé visible est échappé avec `esc_html()`.
- Les classes sont nettoyées et dédupliquées.
- `class`, `href`, `target`, `rel` et `type` sont protégés.
- Aucun JavaScript n’est requis.

## Icône seule

Sans `name` (ou sans `link.title` en mode lien), une icône ou `svg => true`
peut être rendue seule, sans `span` intérieur. `attributes['aria-label']`
est alors obligatoire et doit décrire l’action. Le label de l’icône seule
ne remplace pas cet attribut sur le bouton ou le lien. Sans icône/SVG,
un `aria-label` seul ne produit aucun HTML.

```php
component('btn', [
    'icon' => ['name' => 'blank', 'width' => 16, 'height' => 16],
    'classes' => 'btn-icon',
    'attributes' => ['aria-label' => 'Ouvrir les détails'],
]);

// Lien avec une icône seule.
component('btn', [
    'link' => ['url' => '/projet/'],
    'icon' => ['name' => 'blank'],
    'attributes' => ['aria-label' => 'Découvrir le projet'],
]);
```

La classe `btn-icon` est une classe fournie par l’appelant ; elle n’active
pas le mode icône seule et n’ajoute aucun style automatiquement.

## Couleurs contextuelles

`.btn-solid` conserve sa palette standard. Avec la classe `.btn-invert`, les couleurs
sont héritées du contexte : `.btn-solid.btn-invert` utilise `--bg-action`,
`--bg-action-text`, `--bg-action-hover` et `--bg-action-hover-text`.
`.btn-outline` utilise `--bg-action` pour son texte et sa bordure (`currentColor`).
`.btn-ghost` utilise aussi `--bg-action` pour son texte et mélange cette couleur
à 10 % avec du transparent pour son fond au survol.
Les classes `.bg-color-1`, `.bg-color-2` et `.bg-color-3` définissent ces valeurs,
y compris pour des fonds imbriqués. Aucun paramètre PHP supplémentaire n’est requis.
La palette globale reste dans `--color-*`, le contexte de fond utilise
`--bg-*` et le rendu du bouton utilise ses variables `--btn-*`.

| Variable du fond | Utilisation |
| --- | --- |
| `--bg-action` | Fond de Solid Invert, texte de Outline et Ghost |
| `--bg-action-text` | Texte de Solid Invert |
| `--bg-action-hover` | Fond de Solid Invert au survol |
| `--bg-action-hover-text` | Texte de Solid Invert au survol |

Les valeurs par défaut sont définies sur `:root` dans `variables.scss`.
Chaque `.bg-color-*` redéfinit la palette complète : un fond imbriqué retrouve
ainsi ses propres couleurs, sans modifier les variables globales `--color-*`.

```php
// La couleur dépend du contexte .bg-color-* englobant.
component('btn', ['name' => 'Découvrir', 'classes' => 'btn-solid btn-invert']);
```

`.btn` porte les règles de fond, couleur, bordure et survol. Les variantes
définissent uniquement `--btn-bg`, `--btn-color`, `--btn-border`,
`--btn-hover-bg` et `--btn-hover-color`. Ces variables sont réinitialisées sur
chaque `.btn` pour éviter l’héritage accidentel d’un autre bouton.

Le même survol s’applique lorsque le bouton est survolé directement ou que son
parent `.card` est survolé. Le focus clavier reste visible avec un contour et
les états `disabled` / `aria-disabled="true"` réduisent l’opacité et neutralisent
les interactions à la souris.

## Dépendances

- `theme_link()` pour normaliser et sécuriser les liens ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/btn/btn.scss` pour les styles.

Les icônes de ce composant utilisent `16 × 16` par défaut. Les dimensions
`icon.width` et `icon.height` explicitement fournies restent prioritaires.
