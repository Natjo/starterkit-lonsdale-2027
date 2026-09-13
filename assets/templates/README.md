# Manifestes des templates

Chaque template PHP pouvant être conservé par WP Forge possède un manifeste dans :

```text
assets/templates/{nom}/{nom}.json
```

Le manifeste est la source de vérité des dépendances qui ne peuvent pas être
déduites de façon fiable par une analyse statique, notamment lorsqu'un nom de
card, composant, strate ou hero est calculé en PHP.

```json
{
  "schemaVersion": 1,
  "type": "template",
  "name": "Recherche",
  "slug": "search",
  "file": "search.php",
  "activation": { "option": "enableSearch" },
  "dependencies": {
    "components": ["list", "pagination", "search", "text", "title"],
    "cards": ["news"],
    "strates": [],
    "heroes": [],
    "common": ["header-nav"],
    "modules": [],
    "vendors": []
  }
}
```

Règles :

- `schemaVersion` vaut actuellement `1` ;
- `type` vaut `template` ;
- `slug` correspond au nom du dossier et du fichier JSON ;
- `file` est le nom du fichier PHP à la racine du thème ;
- `activation` est soit `always`, soit un objet `{ "option": "..." }` ;
- les six familles historiques de `dependencies` sont présentes, même vides ;
- `vendors` déclare les bibliothèques front requises ; son absence reste
  interprétée comme un tableau vide pour les manifestes de schéma 1 existants ;
- les identifiants utilisent les noms des helpers, sans préfixe de dossier
  (`news`, pas `card-news`) ;
- une dépendance calculée doit être déclarée explicitement ici.

Forge valide le schéma et les références. Le scan PHP/JS sert uniquement de
contrôle de cohérence pour les appels littéraux.
