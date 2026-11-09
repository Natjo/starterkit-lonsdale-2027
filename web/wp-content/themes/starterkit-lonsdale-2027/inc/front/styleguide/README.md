# Styleguide

Le modèle WordPress `inc/front/styleguide/styleguide.php` présente les démos
des composants et des cards. Il charge son header, son footer et la feuille
locale `styleguide.css`, versionnée par sa date de modification.

`register.php`, chargé depuis `functions.php`, déclare le modèle via
`theme_page_templates` car WordPress ne découvre pas automatiquement les
fichiers aussi profondément imbriqués. Il apparaît sous le nom **Styleguide**.
La méta `_wp_page_template` doit contenir son chemin complet relatif au thème.

La page locale `/components/` utilise ce modèle. Le conteneur body WordPress
reçoit la classe `.page-template-styleguide`. Les images de démonstration
proviennent du fichier local `429-1240x800.jpg` dans ce dossier.

Ce modèle de développement n’appartient pas au catalogue éditorial Forge.
Il n’utilise pas les imports CSS de pages Rspack ; son CSS est chargé uniquement
sur les pages qui utilisent ce modèle.
