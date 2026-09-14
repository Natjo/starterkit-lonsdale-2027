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

## Données WordPress

- emplacement de menu `menu-footer` ;


## Dépendances

- emplacement WordPress `menu-footer` ;
- `assets/common/footer/footer.scss`.
