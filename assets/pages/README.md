# Pages

Chaque fichier de page WordPress géré par Forge possède un dossier dans
`assets/pages/{nom}`. Ce dossier regroupe son manifeste `{nom}.json` et ses
assets éventuels (`{nom}.scss`, `{nom}.js`).

Un JavaScript de page peut être hydraté avec
`data-module="pages/{nom}"`. Son fichier doit être co-localisé sous la forme
`assets/pages/{nom}/{nom}.js` et retourner une fonction de nettoyage lorsqu’il
enregistre des écouteurs ou lance des requêtes persistantes.

Les templates sélectionnables utilisent un nom `template-{slug}` et une
activation `selected`. Les autres pages utilisent une activation `always` ou
conditionnelle (`option`, `contentType` pour un single, `contentTypeArchive`
pour une archive native activée). Tous les manifestes ont le type
`page` : `assets/templates` n’est pas utilisé.

Le fichier `assets/pages/catalog.json` décrit les pages éditoriales proposées
dans le catalogue Forge. Il ne remplace pas les manifestes des fichiers PHP :
chaque entrée contient `name`, `slug`, `type`, `contentMode`, `template`,
`isHome`, `heroValues`, `strates` et `children`. Les pages sont copiées dans le
site lorsqu’elles sont ajoutées depuis Forge ; elles ne sont pas synchronisées
automatiquement avec la base WordPress du starter.

Pour rafraîchir ce catalogue depuis les pages publiées du back-office, lancer
depuis la racine du projet (avec WordPress et ACF actifs) :

```bash
docker compose -f .docker/docker-compose.yml exec -T wordpress \
  wp eval-file /var/www/html/wp-content/themes/starterkit-lonsdale-2027/tests/export-forge-pages.php \
  > assets/pages/catalog.json
```

Puis versionner le JSON généré. L’export conserve l’arborescence, le
modèle, les valeurs ACF des héros et les lignes du flexible `strates`.
