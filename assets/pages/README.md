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

La propriété optionnelle `catalog` du manifeste décrit la page éditoriale
proposée dans le catalogue Forge. Avec `"catalog": true`, Forge réutilise le
nom et le fichier du manifeste, retire le préfixe `template-` de son slug et
applique les valeurs par défaut d’une page WordPress. Une page technique (`404`,
archive, single ou recherche) omet simplement cette propriété.

Un objet permet de ne déclarer que les exceptions aux valeurs dérivées :

```json
"catalog": {
  "name": "Accueil",
  "slug": "accueil",
  "template": "default",
  "isHome": true
}
```

`catalog` accepte aussi un tableau d’exceptions lorsque plusieurs pages de
départ utilisent le même template. Les pages sont copiées dans le site lorsqu’elles sont
ajoutées depuis Forge ; elles ne sont pas synchronisées automatiquement avec la
base WordPress du starter.
