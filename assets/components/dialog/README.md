# Composant `dialog`

Génère un déclencheur et une fenêtre modale native `<dialog>`. Le composant
fonctionne uniquement avec `component('dialog', [...])` et un payload
associatif.

## Utilisation

```php
component('dialog', [
    'title' => 'Informations',
    'content' => '<p>Contenu de la fenêtre.</p>',
    'trigger' => [
        'type' => 'btn',
        'label' => 'En savoir plus',
        'classes' => 'btn-primary',
    ],
    'close_label' => 'Fermer',
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés. L’ancienne clé `trigger_label` et les positions numériques
du déclencheur ont également été retirées.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `content` | `scalar\|Stringable` | `''` | Contenu éditorial obligatoire |
| `title` | `scalar\|Stringable` | `''` | Titre visible `<h2>` |
| `trigger` | `array` | déclencheur bouton anglais | Configuration du déclencheur |
| `close_label` | `scalar\|Stringable` | `'Close'` | Libellé de fermeture |
| `aria_label` | `scalar\|Stringable` | `'Dialog'` | Nom accessible sans titre |
| `id` | `scalar\|Stringable` | auto | Identifiant du dialogue |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Le composant ne contient aucune clé de traduction. L’appelant doit transmettre
les libellés déjà traduits. Passer `trigger => []` retire le déclencheur généré;
passer `close_label => ''` retire le bouton de fermeture.

## Déclencheur

```php
'trigger' => [
    'type' => 'link', // btn ou link
    'label' => 'Ouvrir',
    'classes' => 'dialog-link',
]
```

Toute valeur de `type` autre que `link` produit un bouton. Le composant délègue
le rendu à `component('btn', [...])` ou `component('link', [...])`.

Le déclencheur reçoit automatiquement `data-dialog-id`, `aria-haspopup`,
`aria-controls` et `aria-expanded`. Un tableau vide permet de piloter le
dialogue depuis un autre script.

## Nom accessible

- Avec un `title`, le dialogue utilise `aria-labelledby`.
- Sans titre, un `aria_label` explicite est prioritaire.
- À défaut, le libellé du déclencheur est utilisé.
- Sans aucun de ces textes, le fallback brut `'Dialog'` garantit un nom.

## Rendu et sécurité

- Un `content` vide ou invalide ne produit aucun HTML.
- Le contenu est filtré avec `wp_kses_post()`.
- Le titre et les libellés sont échappés avec `esc_html()`.
- Un identifiant absent est généré avec `wp_unique_id()`.
- `id`, `class`, `open`, `role`, les attributs ARIA et les attributs du module
  sont protégés.

## Accessibilité et cycle de vie

- `showModal()` fournit la modalité et la gestion native du focus.
- `Escape`, le bouton de fermeture et un clic sur le backdrop ferment la
  fenêtre.
- Après fermeture, le focus revient au dernier déclencheur utilisé.
- `aria-expanded` reste synchronisé avec l’état du dialogue.
- Un élément du contenu peut utiliser `autofocus`.
- Les animations respectent `prefers-reduced-motion`.
- Le nettoyage ferme un dialogue encore ouvert, annule les attentes
  d’animation et restaure le déclencheur.

Le composant conserve le focus natif de `<dialog>` et n’ajoute pas de focus
trap personnalisé.

## Dépendances

- `assets/components/dialog/dialog.js` ;
- `assets/components/dialog/dialog.scss` ;
- `component('btn', [...])` et `component('link', [...])` ;
- `component_classes()` et `component_attributes()` ;
- `wp_kses_post()`.
