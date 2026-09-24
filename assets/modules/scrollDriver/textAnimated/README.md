# Text Animated

Anime un texte caractère par caractère en fonction de la progression du scroll.

Le module cherche un élément `.text-animated`, attend qu’il approche du viewport, puis combine `splitText` et `scrollDriver`. Il retourne un cycle de vie `pause`, `resume` et `destroy` compatible avec l’hydrateur.

À utiliser depuis le JavaScript d’une strate, d’un hero ou d’un composant. Il ne doit pas être déclaré directement dans `data-module`.
