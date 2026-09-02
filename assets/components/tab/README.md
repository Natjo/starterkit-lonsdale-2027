# Composant `tab`

Génère un groupe d’onglets accessible avec activation automatique. Le
composant fonctionne uniquement avec `component('tab', [...])` et un payload
associatif.

## Utilisation

```php
component('tab', [
    'title' => 'Informations',
    'items' => [
        ['title' => 'Présentation', 'content' => '<p>Contenu.</p>'],
        ['title' => 'Contact', 'content' => '<p>Coordonnées.</p>'],
    ],
]);
```

Les arguments positionnels et les listes directes ne sont
plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `list<array>` | `[]` | Onglets et panneaux obligatoires |
| `id` | `scalar\|Stringable` | auto | Base des identifiants ARIA |
| `title` | `scalar\|Stringable` | `''` | Titre visible `<h2>` |
| `aria_label` | `scalar\|Stringable` | `'Onglets'` | Nom accessible sans titre visible |
| `orientation` | `'horizontal'\|'vertical'` | `'horizontal'` | Orientation et navigation clavier |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Avec un titre visible, la tablist utilise `aria-labelledby`. Sans titre, elle
utilise `aria_label`.

## Item

```php
[
    'title' => 'Présentation',
    'content' => '<p>Contenu éditorial.</p>',
    'selected' => true,
    'disabled' => false,
    'tab_attributes' => ['data-track' => 'presentation'],
    'panel_attributes' => ['data-section' => 'presentation'],
]
```

Les clés de contenu sont strictement `title` et `content`. Les anciens alias
`label` et `text` ne sont plus interprétés. Les chaînes `"true"`, `"false"`,
`"1"` et `"0"` sont normalisées pour `selected` et `disabled`.

Le premier item non désactivé avec `selected => true` devient actif. À défaut,
le premier item non désactivé est sélectionné. Un item désactivé ne peut jamais
être actif. Si tous les items sont désactivés, aucun HTML n’est produit.

Le contenu est filtré avec `wp_kses_post()`.

## Navigation clavier

Orientation horizontale :

- `Flèche gauche` sélectionne l’onglet précédent ;
- `Flèche droite` sélectionne le suivant.

Orientation verticale :

- `Flèche haut` sélectionne l’onglet précédent ;
- `Flèche bas` sélectionne le suivant.

Dans les deux orientations, `Début` et `Fin` sélectionnent le premier ou le
dernier onglet actif. La navigation ignore les onglets désactivés, boucle aux
extrémités et laisse `Tab` conserver son comportement natif.

## Accessibilité

- Une seule tab possède `aria-selected="true"` et `tabindex="0"`.
- Chaque bouton `role="tab"` contrôle son panneau avec `aria-controls`.
- Chaque panneau `role="tabpanel"` est nommé avec `aria-labelledby`.
- Les panneaux masqués utilisent `hidden`.
- Les panneaux reçoivent `tabindex="0"` pour être atteints directement.
- Les identifiants, rôles, états ARIA, `hidden`, `tabindex` et `disabled` sont
  protégés contre les attributs personnalisés.

## Cycle de vie

Le module `components/tab` est hydraté à proximité du viewport. Son nettoyage
retire les événements et restaure les états HTML initiaux.

## Dépendances

- `assets/components/tab/tab.js` ;
- `assets/components/tab/tab.scss` ;
- `component_classes()` et `component_attributes()` ;
- `wp_kses_post()` pour le contenu des panneaux.
