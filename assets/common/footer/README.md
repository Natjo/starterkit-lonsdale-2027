# Common `footer`

Affiche le menu WordPress de pied de page et un copyright construit avec les
données du site.

## Appel

```php
common('footer');

Le manifeste déclare `activation: "always"` : le footer est une racine globale
du graphe de dépendances Forge et ne dépend pas d'un faux manifeste de page.
```

Le common ne reçoit aucun argument.

## Rendu du menu

Le footer utilise le même walker que les headers
(`inc/front/header-nav-walker.php`). Le `ul.footer-menu` et les `li` n'ont
pas d'attribut `id`. Les classes techniques `menu-item`, `menu-item-type-*`,
`menu-item-object-*`, `menu-item-{ID}` et `menu-item-has-children` sont
retirées ; les classes personnalisées et les états de page active restent.
Chaque `ul.sub-menu` enfant est entouré d'un `div.wrapper-level`.

## Données WordPress

- emplacement de menu `menu-footer` ;


## Dépendances

- emplacement WordPress `menu-footer` ;
- `assets/common/footer/footer.scss`.
