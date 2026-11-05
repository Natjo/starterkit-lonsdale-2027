# Audit du builder — 5 novembre 2026

## Correctifs appliqués après audit

Les constats ci-dessous décrivent l’état initial. La salve de correction a été
vérifiée avec le lockfile mis à jour :

| Constat | Traitement |
| --- | --- |
| R1 | Nettoyage CSS/JS/copied assets coordonné après réussite globale ; aucun inventaire publié après un échec |
| R2 | Verrou inter-processus sur les sorties, partagé par les compilateurs ; `make prod` arrête le watcher avant le build |
| R3 | Dépendances et lockfile actualisés ; `pnpm audit` : zéro avis |
| R4 | Parseur d’index Sass partagé, commentaires respectés et syntaxes invalides signalées |
| R5 | Compilateur split conservé sans entrée initiale ; zéro → un → zéro import testé en watch |
| R6 | Slug du thème validé, chemins confinés, racines et descendants symboliques refusés avant suppression |
| R7 | Inventaire issu des compilations, tailles et SHA-256 vérifiés pour toutes les sorties, sans regex sur le runtime |
| R8 | Anciennes sorties inventoriées retirées après réussite ; migration des CSS/JS/maps historiques ; fichiers manuels non inventoriés préservés |

Rspack passe à 2.3.1. Les loaders déclarent explicitement leur dépendance pair
Webpack compatible ; le builder reste Rspack. La minification JS du dev se limite à une passe ; la
prod conserve les erreurs et avertissements console. Le démarrage Node Docker
utilise `init`, échoue si l’installation des dépendances échoue et transmet
les signaux au watcher.

Validation : 17 contrats du builder réussis dans Docker, dont de vrais builds et
rebuilds watch ; build prod des quatre compilateurs et contrôle de 119 fichiers
par inventaire réussis ; tests Chrome des runtimes prod et dev et du chargement de
chunks réussis, avec ouverture/fermeture de l’accordéon hydraté. Contrats PHP des
assets réussis dans Docker et contrats des composants réussis sur l’hôte.

Restent les 5 avertissements de poids des images placeholder. Le build n’est
pas transactionnel entre compilateurs : les anciennes sorties ne sont pas
nettoyées en cas d’échec, mais certains nouveaux fichiers peuvent déjà avoir
été écrits. Les anciens médias sans inventaire ne sont pas supprimés à
l’aveugle. Voir [README.md](README.md) pour les limites, le verrou et Forge.

## Périmètre et résultat

Configuration dev/prod, quatre compilateurs, loaders Sass/SVG, copie des assets,
répartition critical/normal/shared/split/once, watch, nettoyage, contrôles de
sortie et contrats PHP de chargement. Audit des dépendances avec le lockfile
initial ; aucune mise à jour ni correction fonctionnelle n’avait été appliquée à ce stade.

Le build courant fonctionne, mais la gestion des erreurs et des sorties
partagées doit être renforcée avant de considérer le builder comme robuste.
Les reproductions destructives ont été exécutées uniquement dans des dossiers
temporaires, supprimés après les tests.

## Constats prioritaires

### R1 — P1 : le nettoyage CSS peut supprimer les anciennes sorties pendant un build en échec

Source : `rspack.base.js:31`, `prune-on-demand-assets.js:36`.

Le compilateur app nettoie les CSS dans son propre `afterEmit`, sans attendre
la réussite de critical/admin/split. Contrairement au nettoyage des chunks,
il n'utilise pas le coordinateur multi-compilateur. Reproduction réelle : app
réussit, un autre compilateur échoue, un ancien CSS once est déjà supprimé.
Une page peut ainsi perdre ses styles alors que la nouvelle compilation a échoué.

Correction : coordonner aussi le nettoyage CSS et utiliser l'inventaire de la
compilation réussie, plutôt que relire un `app.scss` susceptible d'avoir changé.
Préserver toutes les anciennes sorties jusqu'à la validation de l'ensemble.

### R2 — P1 : dev et prod peuvent écrire simultanément dans le même thème

Source : `rspack.base.js:109`, `.docker/docker-compose.yml`, service nodejs.

Le watcher Docker tourne actuellement (`rspack build --watch`), tandis que
`pnpm run prod` démarre un autre processus sur les mêmes chemins. Aucun verrou
inter-processus ne protège les écritures ni le nettoyage. Chaque coordinateur
connaît seulement les assets de son processus : l'un peut retirer les chunks
encore référencés par l'autre. Le README décrit cette limite, mais elle n'est
pas empêchée par les commandes usuelles.

Correction : verrou commun avec message explicite, ou orchestration qui arrête
le watcher avant le build prod et le relance ensuite. Pour les déploiements,
préférer une compilation dans un dossier temporaire suivie d'une publication
de l'ensemble validé.

### R3 — P1 maintenance sécurité : dépendances signalées par le registre

`pnpm audit --json` retourne 28 avis : 20 élevés, 6 modérés, 2 faibles,
aucun critique. Ils concernent 10 packages distincts de la chaîne de build,
pas 28 failles démontrées sur le front WordPress.

| Package | Version corrigée minimale couvrant les avis remontés |
| --- | --- |
| js-yaml | 4.3.2 |
| fast-uri | 3.1.9 |
| postcss | 8.5.23 |
| nanoid, branche 3 | 3.3.18 |
| svgo | 4.1.0 |
| browserslist | 4.28.7 |
| immutable | 5.1.8 |
| baseline-browser-mapping | 2.11.0 |
| source-map-js | 1.2.2 |
| postcss-selector-parser | 7.1.6 |

Ces versions proviennent des avis du registre consultés à la date de l'audit ;
elles ne constituent pas une mise à jour déjà testée. L'exploitabilité dépend
des fonctionnalités et entrées utilisées : YAML, source maps, SVG non fiables,
requêtes Browserslist, etc. Les alertes SVGO concernent notamment `removeScripts`,
qui n'est pas explicitement activé ici. L'optimisation SVG ne doit pas être
considérée comme une sanitisation de fichiers non fiables.

Références représentatives :
[PostCSS](https://github.com/advisories/GHSA-fxqj-rqcc-2cmp),
[js-yaml](https://github.com/advisories/GHSA-2883-xcg3-v3hh),
[SVGO](https://github.com/advisories/GHSA-w27v-7q3p-w38r).

Correction : mettre à jour les dépendances directes et transitives via le
lockfile, puis relancer contrats et builds ; pas d'`audit fix --force` aveugle.

## Autres défauts confirmés

### R4 — P2 : le parseur ne respecte pas les commentaires Sass

Sources : `parse-split-imports.js:6`, `sass-dispatch-utils.js:30`.

- Un import entouré de `/* ... */` sur plusieurs lignes reste compilé.
- `@import "components/test/test"; // once` est traité comme un import once.
- Deux imports sur une seule ligne : seul le premier est retenu.
- Les règles CSS écrites directement dans `app.scss` sont silencieusement
  écartées par le dispatch.

Ces comportements ont été reproduits. Ils peuvent expliquer un CSS qui reste
actif après commentaire ou un style absent sans erreur de build.

Correction : un seul parseur partagé, respectant commentaires et instructions,
avec erreur explicite pour les syntaxes non prises en charge et les modes
contradictoires. Documenter le rôle d'index d'imports de `app.scss`.

### R5 — P2 : le premier split/once ajouté en watch n'est pas pris en charge

Source : `rspack.base.js:369`.

Si aucun import on-demand ni hero critical n'existe au démarrage, le compilateur
split n'est pas créé. Une fonction d'entrées dynamiques ne suffit pas : le
watcher n'a alors aucun compilateur capable de produire le premier fichier ajouté.
Absence du compilateur confirmée avec une configuration minimale temporaire.

Correction : conserver ce compilateur en dev même sans entrée initiale, en
gérant explicitement une collection vide. Tester zéro → un → zéro import.

### R6 — P2 : les chemins de nettoyage CSS sont moins protégés que les chunks

Sources : `prune-on-demand-assets.js:36`, `clean-theme-assets.js:6`,
`rspack.config.js:3`.

Si le dossier racine `assets/css` est un lien symbolique, le nettoyage CSS le
traverse et supprime les fichiers correspondants dans la destination externe.
Reproduction confirmée sur un fichier temporaire externe. Les liens symboliques
rencontrés comme sous-dossiers ne sont pas traversés de la même façon.

`WP_THEME_NAME` est seulement vérifié comme non vide. Une valeur contenant
`../` peut déplacer les sorties et le nettoyage hors du dossier des thèmes.
Il s'agit d'un risque de configuration locale/CI, pas d'un endpoint public.

Correction : valider le slug, vérifier le confinement des chemins et refuser
les racines symboliques avant toute suppression, comme pour les chunks.

### R7 — P2 : le contrôle des chunks repose sur le texte du runtime minifié

Source : `check-build-assets.js:79`.

Le script extrait les couples `identifiant numérique: hash` de `js/app.js`.
Il ne dispose pas de l'inventaire Rspack et ne vérifie pas les autres runtimes.
Une évolution du runtime ou un chunk asynchrone issu d'une autre entrée peut
donc échapper au contrôle. C'est une limite structurelle constatée à la lecture,
pas un chunk actuellement manquant : le contrôle courant passe.

Correction : émettre un inventaire complet depuis les compilations et contrôler
les fichiers par cet inventaire. Le même inventaire peut alimenter le nettoyage.

### R8 — P3 : les anciens fichiers hors chunks peuvent s'accumuler

Sources : `rspack.base.js:120`, `prune-chunks.js`, `prune-on-demand-assets.js`.

`output.clean` est correctement désactivé pour éviter qu'un compilateur efface
les autres. En contrepartie, les maps dev des entrées globales, les anciens
fichiers copiés et les stubs JS des entrées CSS supprimées ne sont pas tous
nettoyés par les deux mécanismes ciblés. Un build prod ne garantit donc pas un
répertoire contenant uniquement ses sorties. Des maps peuvent rester publiées.

Correction : inventorier les fichiers possédés par le builder et retirer les
orphelins après réussite globale, sans effacer les assets manuels. Garder
`prod:clean` comme opération explicite : elle efface avant de compiler et ne
préserve pas la dernière version fonctionnelle en cas d'échec.

## Optimisations secondaires

- Le build dev minifie JS et CSS avec deux passes JS : mesurer l'intérêt face
  au temps de recompilation ; ne pas modifier la lisibilité ni les diagnostics
  sans vérifier le confort de développement.
- `drop_console: true` retire aussi les erreurs et avertissements de production,
  notamment ceux de l'hydrateur ; envisager de conserver `warn` et `error`.
- Les 5 avertissements de taille concernent des images placeholder de 407 à
  504 KiB ; pas une erreur de bundling. Réduire ces images séparément.
- Le parsing des flags et la détection hero critical sont dupliqués. Plusieurs
  helpers/configurations semblent sans consommateur interne (`domain`, `proxy`,
  `port`, `config.entries.split`, `collectScssDependencyTree`) : vérifier les
  consommateurs externes Forge avant de supprimer ces exports.

## Vérifications exécutées

| Contrôle | Résultat |
| --- | --- |
| Build prod Docker | Succès des 4 compilateurs, maximum observé 2,65 s ; 1 avertissement groupant 5 images |
| Contrôle des assets post-build | OK, 74 fichiers requis + contrôle heuristique des chunks |
| Contrats chunks, dont vraies compilations et watch isolés | 5/5 réussis sur l'hôte |
| Syntaxe JS des sources | OK, 34 fichiers |
| Contrats PHP `theme-assets-contract.php` | OK dans Docker |
| Reproductions R1, R4, R5 et R6 | Confirmées dans un dossier temporaire |
| Audit du lockfile | 28 avis, dont 20 élevés ; exploitabilité non démontrée pour chaque avis |

Le service Node ne monte pas le dossier racine `tests/` : sa commande de tests
chunks échoue faute de fichier ; les mêmes contrats passent sur l'hôte.
La suite PHP complète des composants a déjà été bloquée par le montage absent
des sources `assets/heros` dans le conteneur WordPress. Le contrat PHP ciblé
des assets, lui, passe. Aucune campagne exhaustive de navigation navigateur
ni simulation d'exploitation des avis de dépendances n'a été réalisée ici.

Ordre proposé : R1/R2 (intégrité des sorties), R3 (dépendances), R4/R5
(dispatch/watch), R6/R7/R8 (confinement et inventaire), puis optimisations.
