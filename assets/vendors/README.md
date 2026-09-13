# Vendors front

Les bibliothèques exécutées dans le navigateur sont stockées localement et
organisées par dossier :

```text
assets/vendors/{slug}/
├── {slug}.json
├── README.md
├── index.js
└── fichiers distribués par la bibliothèque
```

`index.js` constitue l’entrée stable utilisée par le code du starter. Le
manifeste permet à WP Forge de conserver uniquement les vendors atteints dans
le graphe de dépendances ou ajoutés manuellement.

```json
{
  "schemaVersion": 1,
  "type": "vendor",
  "name": "Example",
  "slug": "example",
  "entry": "index.js",
  "dependencies": {
    "components": [],
    "cards": [],
    "strates": [],
    "heroes": [],
    "common": [],
    "modules": [],
    "vendors": []
  }
}
```

Une dépendance à un vendor se déclare dans le manifeste de son consommateur :

```json
"vendors": ["example"]
```

Conserver dans chaque dossier la licence, la version et la source de la
bibliothèque. Les vendors front ne doivent pas être chargés via `package.json`.
