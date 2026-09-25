# Lenis

Active un défilement fluide global pour la page en chargeant le vendor Lenis à la demande.

Son manifeste utilise `"activation": "always"` parce que `assets/app.js`
l’initialise globalement. Les manifestes de pages ne doivent pas le déclarer
comme dépendance.

## Utilisation

```js
import { initLenis } from "@modules/lenis/lenis";

const lenis = await initLenis();
```

L’instance créée est exposée dans `window.lenis`. Une fois prête, le module émet l’événement `lenis:ready` avec l’instance dans `event.detail`, ce qui permet aux autres modules de se connecter sans imposer un ordre d’initialisation.

## Activation

Lenis n’est activé que si :

- `prefers-reduced-motion` n’est pas demandé ;
- le périphérique possède un pointeur précis ;
- le vendor peut être chargé.

Dans les autres cas, `initLenis()` retourne `undefined` et le scroll natif reste actif.

## Configuration

L’instance utilise `autoRaf`, un `lerp` de `0.14` et un multiplicateur de molette adapté à Chrome. Le vendor reste isolé dans `assets/vendors/lenis` et ne doit pas être importé directement par les consommateurs.

## Contrat

- appeler `initLenis()` au démarrage global du site ;
- utiliser `window.lenis` uniquement après son initialisation ou écouter `lenis:ready` ;
- conserver un fallback avec le scroll natif ;
- ne pas forcer le défilement fluide lorsque les animations réduites sont demandées.
