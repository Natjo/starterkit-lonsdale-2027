# Scroll Top

Replace la page en haut lorsque le site démarre ou lorsqu’une page revient depuis le cache du navigateur.

## Utilisation

```js
import initScrollTop, { scrollToTop } from "@modules/scrollTop/scrollTop";

const reset = initScrollTop();
```

`scrollToTop()` utilise `window.lenis.scrollTo()` lorsque Lenis est disponible, puis retombe sur `window.scrollTo()` avec un déplacement immédiat.

## Comportement

À l’initialisation, le module :

- désactive la restauration automatique du scroll lorsqu’elle est disponible ;
- replace immédiatement la page en haut ;
- recommence après `load`, sauf si l’URL contient une ancre ;
- recommence après un retour depuis le back-forward cache (`pageshow.persisted`).

La fonction retournée par `initScrollTop()` permet de demander manuellement un retour en haut.

## Limites

Ce module impose une position initiale en haut et ne convient donc pas à un site qui souhaite restaurer la position précédente après navigation. Les ancres présentes au chargement restent prioritaires lors de l’événement `load`.
