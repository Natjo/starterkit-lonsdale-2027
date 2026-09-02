# Composant `video`

Affiche une vidéo HTML5 ou un lecteur YouTube/Vimeo. Le composant fonctionne
uniquement avec `component('video', [...])` et un payload associatif.

## Source unique

```php
component('video', [
    'url' => '/videos/presentation.mp4',
    'title' => 'Présentation du projet',
    'poster' => '/images/presentation.jpg',
]);
```

## Sources multiples

```php
component('video', [
    'sources' => [
        '/videos/presentation.webm',
        '/videos/presentation.mp4',
        ['src' => '/videos/presentation.custom', 'type' => 'video/mp4'],
    ],
    'title' => 'Présentation du projet',
]);
```

Les arguments positionnels et les listes directes ne sont
plus supportés. Une source structurée utilise uniquement `src`; l’ancien alias
`url` n’est plus interprété dans `sources`.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `url` | `scalar\|Stringable` | `''` | Source unique ou ID média WordPress |
| `sources` | `list<scalar\|array>` | absent | Sources HTML5 multiples |
| `title` | `scalar\|Stringable` | `'Lecteur vidéo'` | Nom accessible du lecteur |
| `poster` | `URL\|ID média` | `''` | Image de façade |
| `play_label` | `scalar\|Stringable` | `'Lire la vidéo'` | Libellé du bouton de façade |
| `autoplay` | `bool` | `false` | Lecture automatique |
| `lazy_autoplay` | `bool` | automatique | Limite l’autoplay HTML5 à la zone visible |
| `loop` | `bool` | `false` | Lecture en boucle |
| `controls` | `bool` | automatique | Affiche les contrôles HTML5 |
| `preload` | `'none'\|'metadata'\|'auto'` | `'metadata'` | Préchargement HTML5 |
| `tracks` | `list<array>` | `[]` | Sous-titres et pistes textuelles |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Si `sources` est présent, il est prioritaire et doit être une liste. Les
chaînes `"true"`, `"false"`, `"1"` et `"0"` sont normalisées pour les options
booléennes.

Les extensions `mp4`, `m4v`, `webm`, `ogv`, `ogg` et `mov` reçoivent leur type
MIME. Un type explicite doit être l’un de ces types reconnus. Les doublons et
les URL dangereuses sont ignorés.

## YouTube et Vimeo

```php
component('video', [
    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'title' => 'Présentation vidéo',
    'poster' => $poster_id,
]);
```

YouTube utilise le domaine `youtube-nocookie.com`. Les identifiants Vimeo et
YouTube sont extraits uniquement depuis des URL reconnues.

## Façade et autoplay

Avec un `poster`, le lecteur tiers ou la vidéo ne démarre pas avant l’action de
l’utilisateur. Le bouton possède un nom accessible composé de `play_label` et
de `title`, puis transfère le focus au lecteur.

Sans façade, l’autoplay HTML5 est muet, utilise `playsinline`, démarre à
l’approche de la zone visible et s’arrête hors écran. Avec
`prefers-reduced-motion`, l’autoplay est neutralisé et les contrôles restent
disponibles.

Une façade force `preload="none"`. L’autoplay différé fait de même afin d’éviter
un téléchargement prématuré.

## Sous-titres

```php
'tracks' => [[
    'src' => '/videos/presentation-fr.vtt',
    'kind' => 'subtitles',
    'srclang' => 'fr',
    'label' => 'Français',
    'default' => true,
]],
```

Les valeurs de `kind` acceptées sont `subtitles`, `captions`, `descriptions`,
`chapters` et `metadata`. Une piste invalide est ignorée.

## Sécurité et attributs protégés

- Seuls HTTP(S), les chemins absolus et les chemins relatifs sûrs sont acceptés.
- Les caractères de contrôle et les antislashs sont refusés dans les URL.
- `class`, `data-type`, `data-autoplay`, `data-autoplay-src`, `data-module` et
  `data-context` sont contrôlés par le composant.
- Le poster décoratif utilise `component('image', [...])`.
- L’icône de lecture utilise `component('icon', [...])`.

## Dépendances

- `youtube_id_from_url()` pour YouTube ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/video/video.js` ;
- `assets/components/video/video.scss`.
