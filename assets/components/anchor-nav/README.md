# Composant `anchor-nav`

Affiche une navigation d’ancres et active automatiquement le lien de la
section atteinte pendant le défilement.

## Appel

```php
component('anchor-nav', [
    'items' => [
        ['label' => 'Introduction', 'target' => 'introduction'],
        ['label' => 'Expertises', 'target' => '#expertises'],
    ],
    'aria_label' => 'Sommaire de la page',
    'horizontal' => true,
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `array` | `[]` | Liens associatifs contenant `label` et `target` |
| `items[].label` | `scalar\|Stringable` | — | Libellé obligatoire et échappé |
| `items[].target` | `scalar\|Stringable` | — | ID avec ou sans `#`, limité aux lettres, chiffres, `_` et `-` |
| `aria_label` | `scalar\|Stringable` | `Navigation des sections` | Nom accessible du `<nav>` |
| `horizontal` | `bool` | `false` | Ajoute la classe `horizontal` à la navigation |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires du `<nav>` |

Le composant ne produit aucun HTML lorsqu’aucune entrée n’est valide. Les
cibles dupliquées sont supprimées. Une cible absente du document reste dans le
HTML, mais elle est ignorée par le JavaScript.

Par défaut, les liens sont affichés verticalement. Avec `horizontal => true`,
le composant ajoute la classe `horizontal` et utilise sa variante horizontale.

`class`, `aria-label`, `data-module` et `data-context` sont réservés par le
composant. Les autres attributs passent par `component_attributes()`.

## Comportement

- le lien et son `<li>` reçoivent la classe `active` ;
- le lien actif reçoit `aria-current="location"` ;
- les mises à jour au scroll sont limitées à une par frame ;
- un clic utilise Lenis lorsqu’il est disponible, sinon `scrollIntoView()` ;
- `prefers-reduced-motion` désactive le déplacement animé ;
- le fragment actif est ajouté à l’URL ;
- le lifecycle `{ pause, resume, destroy }` nettoie listeners, timers, RAF et
  propriétés CSS.

Le composant est hydraté par `data-module="components/anchor-nav"`.
