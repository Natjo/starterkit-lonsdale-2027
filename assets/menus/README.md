# Menus

Chaque emplacement de menu proposé par le starter possède un dossier et un
manifeste JSON du même nom. Forge lit ce catalogue et ne suppose aucun nom
d’emplacement particulier.

Emplacements fournis :

- `menu-header` : navigation principale ;
- `menu-footer` : navigation du pied de page ;
- `menu-legal` : liens légaux et de confidentialité ;
- `menu-secondary` : navigation complémentaire facultative.

Le manifeste déclare un emplacement disponible, sans imposer son rendu dans un
common ou un composant.

Un common (ou un autre manifeste de rendu) déclare les emplacements qu'il
utilise dans `dependencies.menus`, par exemple `["menu-header"]`. Forge ajoute
ces menus au site automatiquement et les protège tant qu'ils sont requis.
Les menus sont créés et affectés à leurs emplacements pendant l'installation ;
leurs pages restent à choisir dans Forge ou dans le BO WordPress.
