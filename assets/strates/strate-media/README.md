# Strate `media`

Affiche une image ou une vidéo sur le rail défini par les options communes de
strate.

Lire d’abord le [README général des strates](../README.md).

## Appel

```php
strate('media', [
    'media_type' => 'image',
    'image' => 123,
]);
```

```php
strate('media', [
    'media_type' => 'video',
    'video' => 'https://vimeo.com/123456789',
    'video_title' => 'Présentation du projet',
]);
```

La strate ne produit aucun balisage lorsque la source correspondant au type
sélectionné est vide.

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `media_type` | `'image'\|'video'` | `'image'` | Sélectionne le composant rendu |
| `image` | `ID\|URL\|null` | Placeholder du manifeste | Transmise à `component('picture')` |
| `video` | `ID\|URL\|null` | `null` | Transmise à `component('video')` |
| `video_title` | `scalar\|Stringable` | `''` | Nom accessible du lecteur vidéo |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

Les anciennes clés `is_image` et `video-title` restent interprétées par le
template pour assurer la compatibilité des payloads existants.

## Champs ACF

```text
acf_fc_layout = media
```

| Libellé | Identifiant | Type ACF | Format |
| --- | --- | --- | --- |
| Type de média | `media_type` | Groupe de boutons | `image` ou `video` |
| Image | `image` | Image conditionnelle requise | ID de média |
| Vidéo | `video` | URL conditionnelle requise | URL complète |
| Titre de la vidéo | `video_title` | Texte conditionnel | Texte simple |
| Options | `options` | Groupe cloné | Options communes des strates |

Le champ URL convient aux vidéos externes ou aux fichiers accessibles par une
URL. Pour sélectionner une vidéo dans la médiathèque sur un site client,
remplacer ce champ par un champ Fichier retournant un ID.

Le mode par défaut est `image`. Son placeholder dédié mesure `1672 × 941 px`,
soit un ratio proche de `16:9` adapté à l’affichage pleine largeur. Forge
l’importe dans la médiathèque lors de la création du site.

## Assets et dépendances

La strate n’a pas d’asset propre. Elle utilise :

- `options()` ;
- `component('picture', [...])` ;
- `component('video', [...])`.
