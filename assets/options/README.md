# Options

Chaque option disponible dans Forge possède un dossier et un manifeste JSON du
même nom. Le manifeste relie l’option à son groupe ACF et à sa page d’options ;
les champs restent définis dans `acf-json`.

Le manifeste déclare aussi les composants utilisés par le rendu associé à
l’option. Forge les affiche dans le catalogue et les active comme dépendances :

```json
"dependencies": {
  "components": ["text", "title"]
}
```
