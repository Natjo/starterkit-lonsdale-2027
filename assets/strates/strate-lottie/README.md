# Strate `lottie`

Strate de démonstration du composant `lottie` avec l’animation locale
`assets/components/lottie/animation.json`.

```php
strate('lottie');
```

La strate transmet au composant l’URL publique
`/assets/lottie/animation.json`, active la lecture automatique et la boucle,
et réserve le ratio natif `1920 / 1080`. Les options communes de strate restent
prises en charge par `options()`.
