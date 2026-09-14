# Vendors front

Les bibliothèques exécutées dans le navigateur sont stockées localement et
organisées par dossier :

```text
assets/vendors/{slug}/
├── README.md
└── fichiers distribués par la bibliothèque
```

Le code du starter importe directement le fichier distribué dont il a besoin.
Le dossier est découvert directement par WP Forge, sans manifeste ni fichier
d’entrée imposé. Forge
conserve uniquement les vendors atteints dans le graphe de dépendances ou
ajoutés manuellement.

Une dépendance à un vendor se déclare dans le manifeste de son consommateur :

```json
"vendors": ["example"]
```

Conserver dans chaque dossier la licence, la version et la source de la
bibliothèque. Les vendors front ne doivent pas être chargés via `package.json`.
