# Strate `slider-images`

Affiche un carrousel d’images saisies dans le BO. Cette variante ne contient
ni cards ni requête de publications. Lire le [README général](../README.md)
et le [README du slider](../../components/slider/README.md).

```php
strate('slider-images', [
    'title' => 'Galerie',
    'images' => [['image' => 123], ['image' => 456]],
    'slider_options' => ['navigation' => true, 'pagination' => true],
]);
```

Le layout ACF `slider-images` propose titre, texte, lien, répéteur `images`
avec un champ image retournant un ID, réglages `slider_options` (navigation,
pagination, timeline), `aria_label` et options communes. Les lignes sans image
sont ignorées. Sans image valide, aucun balisage n’est produit.

Les images sont transmises au composant `slider` via `picture`, en taille
`full` et `fit: cover`. Le composant hydrate lui-même son JavaScript à
l’approche du viewport. Les trois images du manifeste sont des placeholders
destinés à Forge.
