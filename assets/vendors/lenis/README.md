# Vendor `lenis`

Lenis `1.3.25` est stocké localement et utilisé par le module `lenis`.

L’entrée stable du starter est `index.js`. Elle réexporte la build ESM
`lenis.mjs` afin que le code applicatif ne dépende pas du nom du fichier fourni
par une future version du vendor.

```js
const { default: Lenis } = await import("@assets/vendors/lenis");
```

Le dossier est conservé par WP Forge uniquement lorsque son manifeste est
atteint dans le graphe de dépendances ou lorsque le vendor est ajouté
manuellement.
