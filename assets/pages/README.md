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

## Modèles sélectionnables

| Modèle | Contenu |
| --- | --- |
| `template-simple` | Hero `page` et strate `text` unique |
| `template-flexible` | Hero `flexible` et strates ACF |
| `template-flexible-navanchor` | Hero `page`, navigation horizontale et strates |
| `template-flexible-sidebar` | Hero `page`, sidebar libre et strates |
| `template-flexible-sidebar-navanchor` | Hero `page`, sommaire latéral et strates |
| `template-hub` | Hero `page`, strates et liste paginée d’un CPT |
| `template-hub-filters` | Hub avec recherche, filtres et tri au-dessus de la liste |
| `template-hub-filters-sidebar` | Hub avec filtres dans une sidebar |
| `template-contact` | Hero `page` et formulaire de contact |

Les Hubs déclarent `content-archive` pour permettre la sélection du CPT dans
Forge et stockent ce choix dans `hub_post_type`. Les cards dépendent du CPT
choisi ; aucun de ces modèles ne doit imposer une card métier dans son manifeste.
Les strates des modèles `flexible-strates` sont sélectionnées dans Forge : leur
liste de dépendances peut donc être vide tant qu’aucune strate n’est imposée.

## Cohérence des manifestes

Le dossier, le nom du manifeste, son `slug` et le fichier PHP doivent
correspondre. Chaque `slug` et chaque `file` sont uniques. Un manifeste ne doit
pas référencer un template PHP supprimé. Les anciennes archives spécifiques
News, Project et Team ont été remplacées par des pages Hub ; Testimonial n’a
pas d’archive publique.

`assets.css.loading: once` correspond à un import dans `assets/app.scss` :

```scss
@import "pages/template-hub/template-hub" once;
```

Le CSS est compilé séparément et injecté inline selon le fichier PHP réellement
exécuté. Il ne rejoint pas `app.css`. Un renommage doit aussi mettre à jour la
méta WordPress `_wp_page_template` des pages existantes ; changer uniquement le
libellé `Template Name` ou le manifeste ne migre pas cette attribution.
