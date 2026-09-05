# Composant `lottie`

Affiche une animation JSON Lottie avec la build SVG légère de `lottie-web`.
Le composant peut s'hydrater seul ou être piloté par le JavaScript d'un module
parent.

## Utilisation autonome

```php
component('lottie', [
    'src' => get_template_directory_uri() . '/assets/lottie/animation.json',
    'autoplay' => true,
    'loop' => true,
    'width' => 1920,
    'height' => 80,
]);
```

Par défaut, le template rend `data-module="components/lottie"` et
`data-context="@visible true"`. Le player est donc chargé lorsque le composant
approche du viewport, puis mis en pause hors écran par l'hydrateur.

## Utilisation dans un module JavaScript

Le module parent rend le composant sans hydratation autonome :

```php
component('lottie', [
    'src' => get_template_directory_uri() . '/assets/lottie/animation.json',
    'autonomous' => false,
    'classes' => 'my-animation',
]);
```

Il importe ensuite l'initialiseur et retourne son lifecycle :

```js
import initLottie from "@components/lottie/lottie";

export default function initParent(root) {
    return initLottie(root.querySelector(".my-animation"), {
        autoplay: true,
        loop: true,
    });
}
```

Les options JavaScript remplacent les valeurs des attributs `data-*`. Les
méthodes disponibles sont `play()`, `pause()`, `resume()`, `stop()` et
`destroy()`. Plusieurs appels sur la même racine retournent le même lifecycle
et ne créent pas plusieurs players.

## Payload PHP

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `src` | `scalar\|Stringable` | `''` | URL HTTP(S) ou chemin sûr du JSON. Requis. |
| `mobile_src` | `scalar\|Stringable` | `''` | JSON servi sous le seuil. Sans lui, `src` couvre toutes les largeurs. |
| `breakpoint` | `int` | `768` | Largeur en pixels à partir de laquelle `src` est utilisé. Rendu seulement avec `mobile_src`. |
| `autoplay` | `bool` | `false` | Lance la lecture après l'initialisation. |
| `loop` | `bool` | `false` | Répète l'animation. |
| `progressive_load` | `bool` | `true` | Construit progressivement les calques SVG. |
| `respect_reduced_motion` | `bool` | `true` | Neutralise la lecture automatique en mouvement réduit. |
| `autonomous` | `bool` | `true` | Ajoute l'hydratation autonome au composant. |
| `width` | `int\|float` | absent | Largeur native, utilisée avec `height` pour réserver le ratio. |
| `height` | `int\|float` | absent | Hauteur native, utilisée avec `width` pour réserver le ratio. |
| `label` | `scalar\|Stringable` | `''` | Nom accessible. Sans libellé, l'animation est décorative. |
| `classes` | `string\|array` | `''` | Classes supplémentaires. |
| `attributes` | `array` | `[]` | Attributs supplémentaires non réservés. |

Les chaînes `"true"`, `"false"`, `"1"` et `"0"` sont normalisées pour les
options booléennes. Une source vide, dangereuse ou utilisant un protocole autre
que HTTP(S) ne produit aucun HTML.

Si `width` et `height` sont fournis, le composant expose leur rapport via
`--lottie-aspect-ratio`. Cela évite un saut de mise en page avant que le player
n'insère son SVG.

## Animations responsives

Une animation Lottie n'a pas d'équivalent des `<source>` d'un `<picture>` : le
JSON est chargé par le script, une seule URL est donc retenue par largeur.

```php
component('lottie', [
    'src' => $desktop_url,
    'mobile_src' => $mobile_url,
    'breakpoint' => 1200,
]);
```

Le composant choisit sa source avant l'import du player : le JSON inutile n'est
jamais téléchargé. Le préchauffage suit la même règle. Un franchissement du
seuil, à la rotation d'un appareil ou au redimensionnement d'une fenêtre,
détruit le player et recharge l'autre source ; l'animation repart alors de sa
première image.

Fournir deux sources identiques, ou une seule des deux, revient à une animation
non responsive : aucun `data-lottie-src-mobile` n'est rendu et aucun observateur
de média n'est posé. Une `mobile_src` seule est promue en source unique.

En JavaScript, les options `mobileSrc` et `breakpoint` remplacent les attributs
correspondants.

## Mouvement réduit

Avec `respect_reduced_motion => true`, le composant reste sur sa première
image lorsque `prefers-reduced-motion: reduce` est actif. Un appel manuel à
`play()` ou `resume()` ne contourne pas cette préférence.

## Préchauffage

Le module exporte `warmLottie`, appelé une seule fois depuis `app.js` :

```js
import { warmLottie } from '@components/lottie/lottie';

warmLottie();
```

Après le chargement de la page, et pendant un temps mort du navigateur, il met
en cache le player et le JSON retenu pour la largeur courante de chaque
`[data-lottie-src]` présent. Une
animation initialisée plus tard, à l'entrée dans le viewport, démarre alors
sans attendre le réseau. Sans composant Lottie dans la page, la fonction ne
déclenche aucune requête.

## Dépendances et fichiers JSON

- `assets/vendors/lottie.js` réexporte la build légère stockée localement dans
  `assets/vendors/lottie/lottie_light.min.js` ;
- la licence du player est conservée dans `assets/vendors/lottie/LICENSE.md` ;
- le player n'est pas installé ni chargé depuis `package.json` ;
- les JSON co-localisés dans `assets/components/lottie/` sont copiés par le
  build dans le dossier public `/assets/lottie/`.
