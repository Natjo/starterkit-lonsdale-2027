# Administration

## WYSIWYG

`wysiwyg.php` configure TinyMCE pour le back-office, y compris les éditeurs ACF.
Les formats de titres proposés sont H2, H3 et H4 ; H1 est retiré du sélecteur.
Le menu des formats personnalisés propose « Intro », qui applique
`class="intro"` au paragraphe, et « Petit texte », qui applique une balise
`small` au texte sélectionné. Les styles d’Intro sont partagés avec le composant `intro`.
Les anciens formats « Niveau » et « Texte » ne sont plus proposés. Les contenus existants
ne sont pas réécrits ; cette configuration ne filtre pas le HTML collé ou saisi
dans l’onglet Texte.

Dans les éditeurs Full et Basic, Entrée crée un paragraphe (`p`) et Maj+Entrée
un retour simple (`br`). `wpautop` est désactivé dans TinyMCE pour que le plugin
WordPress conserve les balises `p` lors de la sauvegarde et du passage en mode
Texte. Cela ne désactive pas le formatage ACF/WordPress du contenu côté front.
L’indentation TinyMCE est activée pour Full et Basic afin de séparer les blocs
HTML par des retours à la ligne dans l’onglet Texte.
Vérifier ce comportement avec le plugin TinyMCE `wordpress` actif : un test de
TinyMCE seul ne couvre pas son traitement `SaveContent` ni l’aller-retour
Visuel/Texte.
La barre ACF « Light » conserve uniquement le gras et
les retours simples (`br`), y compris sur Entrée. Elle n’ajoute aucun paragraphe.
Ses valeurs sont normalisées au chargement, à l’enregistrement et au formatage
front : les éventuels paragraphes sont convertis en retours simples. Ces champs
servent notamment au contenu des titres `h1`/`h2`.

Le body de chaque éditeur TinyMCE porte la classe `rte`, y compris les barres
ACF Full, Basic et Light. Les classes ajoutées par WordPress sont conservées.
`assets/styles/utils/admin-rte.scss` inclut les styles partagés, les polices du
site et les règles `.rte` de `assets/components/text/text.scss`, pour afficher
la mise en forme du front dans l’éditeur. Recompiler l’entrée Rspack `admin`
après une modification de ces sources ; le CSS chargé est versionné par sa date
de modification.
