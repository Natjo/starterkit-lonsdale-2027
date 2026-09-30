# Strate `image`

Affiche une image éditoriale via `component('picture')`. Lire d’abord le
[README général](../README.md) et celui du [composant picture](../../components/picture/README.md).

```php
strate('image', ['image' => 123]);
```

Le layout ACF `image` contient seulement le champ `image` (ID de média) et les
options communes des strates. Une source absente ne produit aucun balisage.
Le manifeste fournit un placeholder de démonstration pour Forge.

Le SCSS conserve l’image à la largeur de la strate sans modifier son ratio.
