# Module `breakpoint`

Observe le passage au-dessus ou en dessous d'une largeur avec `matchMedia`.
Contrairement à un listener `resize`, il ne déclenche un callback que lorsque
la media query change d'état.

## Utilisation

```js
import breakpoint, { BREAKPOINTS } from "@modules/breakpoint/breakpoint";

const destroy = breakpoint(BREAKPOINTS.md, {
    above: media => {
        // Largeur supérieure ou égale à 768px.
    },
    under: media => {
        // Largeur inférieure à 768px.
    },
});

// Lorsque le module ou le composant est détruit.
destroy();
```

Le callback correspondant à la largeur courante est appelé immédiatement, sans
timer. Les appels suivants ont lieu uniquement lors du franchissement du
breakpoint.

Chaque callback reçoit la `MediaQueryList` courante ou l'événement `change`, qui
exposent tous les deux la propriété booléenne `matches`.

## Breakpoints disponibles

| Nom | Valeur |
| --- | ---: |
| `xs` | `480px` |
| `sm` | `576px` |
| `md` | `768px` |
| `lg` | `1025px` |
| `xlg` | `1200px` |
| `wd` | `1440px` |
| `xwd` | `1920px` |
| `xlgwd` | `2400px` |

Ces valeurs doivent rester alignées avec
`assets/styles/_breakpoints.scss`. Utiliser les constantes exportées plutôt que
répéter une valeur en dur dans les modules JavaScript.

## Contrat

- `value` doit être un nombre fini positif ou nul ; une valeur invalide produit
  une fonction de nettoyage sans effet ;
- `above` et `under` sont facultatifs ;
- appeler plusieurs fois la fonction de nettoyage est sans effet secondaire ;
- sans `window.matchMedia`, le module retourne un nettoyage vide et ne lance
  aucun callback ;
- le module ne doit pas être appelé avec `new`.

Dans un module hydraté, retourner directement son nettoyage lorsque celui-ci est
la seule ressource persistante :

```js
export default function initExample(root) {
    return breakpoint(BREAKPOINTS.lg, {
        above: () => root.classList.add("is-desktop"),
        under: () => root.classList.remove("is-desktop"),
    });
}
```
