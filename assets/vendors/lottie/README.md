# Vendor `lottie`

Le player Lottie léger est stocké localement et utilisé par le composant
`lottie`.

L’entrée stable `index.js` réexporte `lottie_light.min.js` :

```js
const { default: lottie } = await import("@assets/vendors/lottie");
```

La licence de la bibliothèque est conservée dans `LICENSE.md`. WP Forge ne
conserve ce dossier que lorsque le composant `lottie` est utilisé ou lorsque le
vendor est ajouté manuellement.
