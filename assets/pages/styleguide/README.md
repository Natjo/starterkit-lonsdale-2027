# Styleguide — module de copie

Module de développement hydraté sur la racine `.styleguide` avec
`data-module="pages/styleguide"`. Il ajoute des boutons de copie aux blocs
`.styleguide-readme pre > code`, uniquement si l’API Clipboard est disponible.
Le code brut est copié sans le HTML de coloration. Le statut accessible indique
le succès ou l’échec, puis se réinitialise après 1,8 seconde.

Le nettoyage annule les timers et les écouteurs et restaure les blocs d’origine.
Le modèle et les styles sont documentés dans
`inc/front/styleguide/README.md` dans le thème. Ce module ne fait pas partie
du catalogue des pages Forge.
