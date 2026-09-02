# Composant `btn`

Génère un `<button>` ou un lien `<a>` avec la classe `btn`. Le composant
fonctionne uniquement avec `component('btn', [...])` et un payload associatif.

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
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `icon` | `array associatif` | absent | Payload de `component('icon', [...])` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

La présence de `link` sélectionne toujours le mode lien. Un `link` incomplet
ou invalide ne produit aucun HTML et ne retombe pas sur `name`.

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

## Règles de rendu

- Un bouton sans `name` valide ne produit aucun HTML.
- Un lien exige une URL sûre et un titre non vide.
- Le libellé visible est échappé avec `esc_html()`.
- Les classes sont nettoyées et dédupliquées.
- `class`, `href`, `target`, `rel` et `type` sont protégés.
- Aucun JavaScript n’est requis.

## Dépendances

- `normalize_action_link()` pour normaliser et sécuriser les liens ;
- `component('icon', [...])` pour l’icône optionnelle ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/btn/btn.scss` pour les styles.
