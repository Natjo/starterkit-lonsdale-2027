# Pages

Chaque fichier de page WordPress géré par Forge possède un dossier dans
`assets/pages/{nom}`. Ce dossier regroupe son manifeste `{nom}.json` et ses
assets éventuels (`{nom}.scss`, `{nom}.js`).

Les templates sélectionnables utilisent un nom `template-{slug}` et une
activation `selected`. Les autres pages utilisent une activation `always` ou
conditionnelle (`option`, `contentType` pour un single, `contentTypeArchive`
pour une archive native activée). Tous les manifestes ont le type
`page` : `assets/templates` n’est pas utilisé.
