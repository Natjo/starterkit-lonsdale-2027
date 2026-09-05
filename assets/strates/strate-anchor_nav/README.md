# Strate `anchor_nav`

Strate statique de démonstration pour le composant `anchor-nav`. Elle affiche
trois liens et trois longues sections permettant de vérifier l’activation des
ancres pendant le scroll.

## Appel

```php
strate('anchor_nav');
```

Les données métier transmises sont ignorées. La strate conserve uniquement les
options communes utilisées par `options()` sur son conteneur.

La navigation est produite avec :

```php
component('anchor-nav', [
    'items' => [
        ['label' => 'Section 1', 'target' => 'anchor-1'],
        ['label' => 'Section 2', 'target' => 'anchor-2'],
        ['label' => 'Section 3', 'target' => 'anchor-3'],
    ],
    'aria_label' => 'Navigation des sections',
]);
```

Le composant gère le balisage, l’échappement et l’hydratation. La strate gère
le conteneur sticky et les trois sections d’exemple. Le contrat interactif est
documenté dans `assets/components/anchor-nav/README.md`.
