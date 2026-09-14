# Lenis

Le point d’entrée public du module est `lenis.js` et expose :

```js
import { initLenis } from "@modules/lenis/lenis";
```

`initLenis()` charge le vendor Lenis à la demande et ne l'active que pour un
pointeur précis, lorsque la réduction des animations n'est pas demandée.
