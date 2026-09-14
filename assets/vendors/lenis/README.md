# Vendor `lenis`

Lenis `1.3.25` est stocké localement et utilisé par le module `lenis`.

Le module importe directement la build ESM `lenis.mjs`.

```js
const { default: Lenis } = await import("@assets/vendors/lenis/lenis.mjs");
```

Le dossier est conservé par WP Forge uniquement lorsque son manifeste est
atteint dans le graphe de dépendances ou lorsque le vendor est ajouté
manuellement.
