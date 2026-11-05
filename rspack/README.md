# Compilation et nettoyage des chunks

`rspack.base.js` fournit les compilateurs critical, app, admin et split/once.
Ils partagent le dossier `assets` du thème. `output.clean` reste désactivé :
un compilateur ne doit pas effacer les sorties des autres.

`prune-chunks.js` est branché sur chaque compilateur avec un coordinateur commun.
Le nettoyage attend que tous aient terminé avec succès. En watch, invalidation,
début de compilation et échec bloquent le nettoyage jusqu’à une nouvelle réussite.
Le coordinateur conserve les dernières listes complètes `compilation.getAssets()`
des compilateurs non reconstruits, y compris leurs assets non réémis depuis le
cache. La liste finale est leur union, pas seulement les fichiers émis au dernier tour.

Seuls `js/chunks/{hash de 8 caractères hexadécimaux}.js` et leurs compagnons
`.map`, `.gz`, `.br` sont supprimables. Un compagnon reste si son JS est actif.
Les entrées app/admin/critical, CSS, sources et noms personnalisés ne sont jamais
ciblés. Les liens symboliques et fichiers non réguliers provoquent un refus.
Les candidats sont tous validés avant le premier retrait, puis revérifiés juste
avant leur suppression définitive. Limite : 20 000 entrées dans `js/chunks`.

Ce coordinateur ne verrouille pas les autres processus : arrêter le watcher
avant un autre build. Un onglet déjà ouvert peut encore utiliser l’ancien
runtime : recharger le front après un nettoyage. Les fichiers sont retirés
définitivement, sans corbeille. Une erreur de compilation ne nettoie aucun chunk,
mais Rspack peut avoir émis d’autres fichiers : ce n’est pas un rollback de build.

`RSPACK_DEFER_CHUNK_CLEANUP=1` permet à un orchestrateur (Forge) de différer ce
nettoyage jusqu’après ses propres validations de build. Il doit alors utiliser
la liste complète des assets fournie par tous les compilateurs, jamais un scan
heuristique des imports.

Contrats : `node --test tests/prune-chunks-contract.cjs` (inclut de vraies
compilations Rspack dans un dossier temporaire, sans toucher au thème).
