# Strate `image`

Affiche une image éditoriale via `component('picture')`. Lire d’abord le
[README général](../README.md) et celui du [composant picture](../../components/picture/README.md).

```php
strate('image', ['image' => 123]);
```

Le layout ACF `image` contient le champ `image` (ID de média), le switch
`fixed_height` (« Hauteur fixe ») et les options communes des strates.
Une source absente ne produit aucun balisage.
Le manifeste fournit `assets/img/placeholders/strate-image.jpg` comme image
de démonstration pour Forge et le Design System utilise le même fichier.

Par défaut, `fixed_height` est désactivé : l’image conserve ses proportions
naturelles à la largeur disponible (comportement par défaut). Quand il est activé, elle
remplit une hauteur de 60rem en `cover`, avec recadrage. La hauteur peut être
adaptée en CSS via `--image-height` sur la strate.

```php
strate('image', ['image' => 123, 'fixed_height' => true]);
```
