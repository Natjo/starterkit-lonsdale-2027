# Builder Rspack

Le builder fournit quatre compilateurs : critical, app, admin et split/once.
Ils partagent les assets du thème désigné par `WP_THEME_NAME`. Cette variable
est obligatoire et accepte uniquement lettres, chiffres, tirets et underscores,
avec une lettre ou un chiffre au début ; aucun chemin n’est autorisé.
Les CSS/JS compilés ne se modifient jamais manuellement.

## Imports Sass

`assets/app.scss` est un index d’imports locaux, sans extension, avec un seul
mode facultatif par import :

```scss
@import "styles/styles" critical;
@import "components/slider/slider";
@import "components/example/example" shared;
@import "components/anchor-nav/anchor-nav" split;
@import "pages/404/404" once;
@import "heros/hero-homepage/hero-homepage" critical;
```

| Mode | Sortie |
| --- | --- |
| normal, sans suffixe | `css/app.css` |
| critical | `css/critical.css` |
| shared | app et critical |
| split | CSS dédié, lien enregistré par le helper PHP de rendu |
| once | CSS dédié sous `css/once/`, injecté inline par le helper PHP |
| hero critical | CSS dédié, ajouté au critical inline quand le hero est rendu |

Les styles d’impression `styles/utils/print` sont importés sans suffixe dans
`app.scss` : ils restent dans `app.css`, sous `@media print`, sans requête
supplémentaire ni inclusion dans le critical.

Un parseur commun respecte les commentaires `//` et `/* ... */`, les imports
sur plusieurs lignes et plusieurs instructions sur une même ligne. Un doublon
identique est dédupliqué ; des modes contradictoires provoquent une erreur.
Les règles CSS, variables Sass ou directives non prises en charge dans cet index
provoquent une erreur explicite : les placer dans un fichier importé. Les imports
imbriqués des stylesheets restent traités normalement par Sass.

`styles/breakpoints` est injecté automatiquement dans les compilations dispatchées.
Le compilateur split existe même sans import on-demand : en watch, le passage
zéro → un → zéro import ne demande aucun redémarrage.

Les configurations dev et prod imposent leur mode de dispatch, de cache et
de source maps, même si `NODE_ENV` est absent ou si elles sont chargées dans
un même processus.

En dev, la minification JS se limite à une passe et les fichiers possèdent des
source maps. En prod,
JS et CSS sont minifiés ; `console.warn` et `console.error` restent disponibles,
mais `log`, `info`, `debug` et `debugger` sont retirés.

## Verrou et commandes

Chaque compilation acquiert `assets/.rspack-build.lock/` avant son démarrage.
Le verrou est partagé par les compilateurs du processus et conservé pendant
tout le watch. Un autre build ou nettoyage est refusé avec un message explicite.
Il est libéré à la fermeture des compilateurs, à la sortie normale et sur
SIGINT/SIGTERM. Le service Docker lance directement le CLI derrière son init
pour transmettre ces signaux au bon processus. Aucun verrou existant n’est supprimé automatiquement : après
un SIGKILL ou un arrêt brutal, vérifier qu’aucun build/watch ne tourne avant
de retirer le dossier indiqué par l’erreur. Un verrou n’est jamais à committer.

`make prod` arrête le service Node, installe le lockfile puis exécute la
compilation dans un conteneur temporaire. Il ne relance pas le watcher pour
ne pas remplacer immédiatement les assets prod par ceux du dev.

```bash
make prod
# Reprendre le watch ensuite :
docker compose -f .docker/docker-compose.yml up -d nodejs
```

En dehors de Docker, arrêter `pnpm run dev` avant `pnpm run prod`.
`prod:clean` est conservé comme alias de `prod` : le build normal nettoie déjà
ses anciens fichiers, sans effacer les sorties fonctionnelles avant compilation.
`clean-theme-assets.js` ne retire que les fichiers obsolètes à partir d’un
inventaire existant et valide ; il utilise le même verrou.

## Inventaire et nettoyage

`output.clean` reste désactivé : un compilateur ne doit pas effacer les autres.
Un coordinateur attend leur réussite globale, y compris en watch. Il conserve
les dernières listes complètes `compilation.getAssets()` des compilateurs non
reconstruits, pas seulement leurs fichiers réémis. Une erreur ou invalidation
bloque nettoyage et publication d’un nouvel inventaire.

`assets/.rspack-assets.json` contient les noms, tailles et empreintes SHA-256 de
l’union des sorties réelles. `check-build-assets.js` contrôle cet inventaire,
les chunks de tous les compilateurs et les fichiers attendus. Un fichier
manquant ou modifié fait échouer le contrôle. Les stubs JS vides produits pour
certaines entrées CSS sont légitimes ; les lister ne les charge pas côté client.
Une compilation partielle (`--configName`) ne publie pas d’inventaire global :
relancer un build complet avant validation ou distribution.

Après réussite, le nettoyage retire les anciennes sorties possédées par le
builder : CSS, JS, maps, images et fichiers copiés. Il garde les fichiers manuels
non inventoriés. Pour migrer un ancien build sans inventaire, les chemins de CSS
on-demand, leurs stubs JS, les chunks hashés et les maps des entrées globales sont
réservés au builder et peuvent être nettoyés. Les anciens médias non inventoriés
restent conservés, faute de pouvoir les distinguer des ajouts manuels.

Les chemins sont confinés et les liens symboliques refusés, y compris sur les
racines CSS/JS. Tous les candidats sont validés avant le premier retrait puis
revérifiés juste avant suppression définitive, sans corbeille. Limite : 20 000
fichiers d’inventaire ou entrées examinées pendant le nettoyage.

Ce mécanisme n’est pas un déploiement transactionnel : les compilateurs réussis
peuvent déjà avoir écrit leurs sorties lorsqu’un autre échoue. Un onglet ouvert
peut également utiliser l’ancien runtime ; recharger le front après un build.
Pour publier sans cette fenêtre, compiler dans un dossier séparé puis déployer
l’ensemble validé avec son inventaire.

`RSPACK_DEFER_CHUNK_CLEANUP=1` conserve son contrat historique pour Forge :
seul le nettoyage des chunks JS est différé jusqu’aux validations de
l’orchestrateur. Les CSS obsolètes sont retirés après réussite globale des
compilateurs, y compris dans ce mode : sinon un ancien CSS once pourrait prendre
la priorité sur un nouveau CSS normal côté PHP. L’inventaire `pending` conserve
les anciens chunks possédés pour un nettoyage ultérieur. Forge doit arrêter le
watcher avant de compiler et utiliser la liste complète des compilateurs pour
son propre nettoyage JS, jamais des imports extraits du runtime. Les exports
historiques de `prune-chunks.js` sont conservés.

## Vérifications

```bash
pnpm run test:build
pnpm run check:js
pnpm run prod
pnpm audit
# Chrome/Chromium installé, WP_THEME_NAME renseigné :
pnpm run test:build:browser
```

Les tests utilisent des dossiers temporaires et de vraies compilations Rspack,
notamment pour les erreurs multi-compilateurs, le watch sans entrée initiale,
le confinement, les liens symboliques, le verrou inter-processus et les sorties
supprimées ou corrompues. Si `tests/` n’est pas monté dans le service Node :

```bash
docker compose -f .docker/docker-compose.yml run --rm --no-deps \
  -v "$PWD/tests:/usr/app/tests:ro" nodejs pnpm run test:build
```

## README du Styleguide

Le compilateur app convertit les README de `assets/{components,cards,strates,heros}/` en HTML via
`marked`, en développement et en production. Les sorties sont publiées sous
`assets/styleguide/{famille}/{slug}/README.html` et suivies par l’inventaire
comme les autres assets copiés. Elles sont consommées et filtrées côté PHP
par le modèle Styleguide. Le watcher suit les README sources.

Les blocs de code des README sont colorés avec `highlight.js` pendant la
conversion Markdown. Le HTML contient des classes `hljs-*`, conservées par le
filtrage PHP. Les langues inconnues utilisent `plaintext` et restent échappées.

Le build exporte aussi les seuils de `assets/styles/breakpoints.scss` vers
`styleguide/breakpoints.json`. Les déclarations de base doivent être des
valeurs littérales en pixels ; une déclaration non prise en charge fait
échouer le build plutôt que produire un tableau incomplet.
