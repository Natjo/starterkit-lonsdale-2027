# Modules du starter

Chaque module possède son dossier, son point d’entrée JavaScript, son README et
son manifeste `{nom}/{nom}.json`.

## Version du module

Le champ `version` du manifeste est une chaîne, par exemple `"1.0.0"`. Il indique
la version du module affichée par Forge et ses Design systems. Les modules du
catalogue commencent à `1.0.0` lors de l’introduction de ce suivi. Mettre cette
valeur à jour lors des prochaines évolutions du module.

Cette version est indépendante de `schemaVersion`, qui décrit le format du
manifeste, et de la version éventuelle d’un vendor utilisé par le module.
Un module sans `version` reste compatible et est affiché comme non versionné.
