# Taxonomies

Chaque dossier décrit une taxonomie proposée par le starter à Forge. Le
manifeste `{key}.json` est un catalogue de création ; WordPress utilise les
appels standards `register_taxonomy()` de `inc/datatypes.php`.

Forge empêche l'ajout d'une taxonomie tant que ses Content Types associés ne
sont pas présents, puis ne génère que les taxonomies choisies.
