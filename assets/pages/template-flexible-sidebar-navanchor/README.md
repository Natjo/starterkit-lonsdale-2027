# Flexible sidebar nav anchor

Modèle sélectionnable `template-flexible-sidebar-navanchor.php`, avec hero
`page`, navigation latérale `anchor-nav` dans `.main-sidebar` et strates ACF
dans `.main-content`. Ces deux colonnes sont regroupées dans `.main-layout`.

Les liens sont générés depuis l’option `name` des strates. Une strate sans nom
reste affichée mais ne figure pas dans le sommaire. Le CSS du template est
compilé en `once` et injecté inline ; le composant `anchor-nav` fournit son
propre comportement.
