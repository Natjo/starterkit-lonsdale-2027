# Strate `video`

Affiche une vidéo HTML5, YouTube ou Vimeo via `component('video')`. Lire d’abord
le [README général](../README.md) et celui du [composant video](../../components/video/README.md).

```php
strate('video', [
    'video' => 'https://vimeo.com/123456789',
    'video_title' => 'Présentation du projet',
    'poster' => 123,
]);
```

Le layout ACF `video` contient `video` (URL complète), `video_title` (nom
accessible), `poster` (image facultative retournant un ID) et les options
communes. La strate ne produit aucun balisage sans source.

Le poster active la façade locale du composant et attend un clic. Sans poster,
les lecteurs externes ne chargent qu’à l’entrée dans le viewport. Le JavaScript
appartient au composant `video`, pas à la strate.
Le manifeste laisse `video` vide : le starter n'embarque pas de fichier vidéo
de démonstration et n'impose pas de source externe.
