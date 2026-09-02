# Composant `tooltip`

Affiche une courte information complémentaire au survol, au focus clavier ou
au toucher. Le composant fonctionne uniquement avec
`component('tooltip', [...])` et un payload associatif.

## Utilisation

```php
component('tooltip', [
    'label' => 'Délai estimé',
    'content' => 'Entre <strong>3 et 5 jours</strong>.',
    'classes' => 'tooltip-right',
    'attributes' => ['data-tracking' => 'delivery-delay'],
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `label` | `scalar\|Stringable` | `''` | Texte visible du bouton |
| `content` | `scalar\|Stringable` | `''` | Explication courte non interactive |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires du conteneur |

`label` et `content` sont obligatoires. Une valeur vide ou complexe ne produit
aucun HTML. La chaîne `"0"` reste valide.

## Contenu autorisé

Le contenu est filtré avec `wp_kses()` et accepte uniquement :

```text
br, strong, em, b, i, small, sup, sub, span[class]
```

Les liens, boutons, médias et formulaires sont supprimés. Un contenu riche ou
interactif doit utiliser un composant `popover` ou `dialog`.

Le `label` est toujours échappé avec `esc_html()`.

## Accessibilité et interactions

- Le déclencheur est un véritable `<button type="button">`.
- `aria-describedby` relie le bouton au contenu `role="tooltip"`.
- Le tooltip apparaît au survol et au focus clavier.
- Sur écran tactile, un premier toucher ouvre et un second ferme.
- `Escape` et un clic extérieur ferment le tooltip.
- Le contenu reste survolable avec le pointeur.
- Le positionnement horizontal est corrigé près des bords de l’écran.
- Les animations respectent `prefers-reduced-motion`.

`aria-expanded` n’est volontairement pas utilisé : il n’appartient pas au
pattern ARIA du tooltip.

## Attributs protégés

Le composant contrôle `class`, `data-tooltip`, `data-module`,
`data-tooltip-open` et `data-tooltip-dismissed`. Les autres attributs sûrs sont
conservés.

## Cycle de vie

Le module `components/tooltip` gère ses événements et son nettoyage à la
destruction du composant. Le comportement JavaScript ne dépend d’aucune option
de traduction ou de contenu PHP.

## Dépendances

- `assets/components/tooltip/tooltip.scss` ;
- `assets/components/tooltip/tooltip.js` ;
- `component_classes()` et `component_attributes()` ;
- `wp_kses()` et `esc_html()`.
