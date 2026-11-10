# Styleguide

Les modèles WordPress du dossier `inc/front/styleguide/` présentent les démos
des composants, cards, strates et heroes. Chaque modèle charge son header, son footer et la feuille
locale `styleguide.css`, versionnée par sa date de modification.

`register.php`, chargé depuis `functions.php`, déclare le modèle via
`theme_page_templates` car WordPress ne découvre pas automatiquement les
fichiers aussi profondément imbriqués. Ils apparaissent sous les noms **Styleguide — Composants**, **Cards**, **Strates**
et **Heroes**.
La méta `_wp_page_template` doit contenir son chemin complet relatif au thème.

La page locale `/components/` utilise le modèle des composants. Le conteneur body WordPress
reçoit la classe `.page-template-styleguide`. Les images de démonstration
proviennent du fichier local `429-1240x800.jpg` dans ce dossier.

Ce modèle de développement n’appartient pas au catalogue éditorial Forge.
Il n’utilise pas les imports CSS de pages Rspack ; son CSS est chargé uniquement
sur les pages qui utilisent ce modèle.

## Documentation des composants

Chaque section `component-*` présente deux colonnes : le titre et les aperçus
à gauche, le README rendu en HTML à droite. Sous 900 px, les deux colonnes
s’empilent. La même disposition est utilisée pour les quatre familles.

Rspack convertit les README des quatre familles avec `marked` et génère
`assets/styleguide/{famille}/{slug}/README.html` dans le thème. Le watcher
reconstruit ces documents quand les sources changent. Les fichiers générés
ne doivent pas être édités manuellement.

`readme.php` lit uniquement ces fichiers locaux et filtre le HTML avec
`wp_kses_post()`. Les titres Markdown sont décalés à partir du niveau 4 ;
les tableaux, listes et blocs de code sont rendus. Les liens vers un autre
README de composant pointent vers sa section du Styleguide.

La coloration syntaxique des blocs de code est générée au build avec
`highlight.js`, selon la langue de la clôture Markdown (`php`, `js`, `scss`,
`html`, etc.). Les langues absentes ou inconnues restent en texte échappé.
Les couleurs sont définies dans `styleguide.css` et limitées aux README ;
aucun script de coloration n’est chargé dans le navigateur.

## Copier les exemples

Le module `assets/pages/styleguide/styleguide.js`, hydraté uniquement sur le
Styleguide via `data-module="pages/styleguide"`, ajoute une icône de copie à
chaque bloc de code. Il copie `code.textContent` avec l’API Clipboard, affiche
un état de succès ou d’erreur et annonce le résultat via `role="status"`.
Sans API Clipboard, aucun bouton n’est ajouté. Le module nettoie ses écouteurs,
états temporaires et éléments injectés lors de sa destruction.

Ce dossier JavaScript de page est réservé au modèle interne du Styleguide ;
il n’ajoute pas d’entrée au catalogue Forge.

Sur desktop, les `.styleguide-demo` restent sticky sous le sommaire. Le module
ajuste leur décalage selon la hauteur du sommaire via `ResizeObserver`.
Sous 900 px, les démos reprennent leur position normale.

## Pages du catalogue

| URL locale | Modèle | Contenu |
| --- | --- | --- |
| `/styles/` | `styles.php` | Couleurs, typographie, espacements, surfaces et layout |
| `/components/` | `styleguide.php` | Composants |
| `/cards/` | `cards.php` | 8 cards |
| `/strates/` | `strates.php` | 22 strates |
| `/heros/` | `heros.php` | 4 heroes |

Les modèles sont enregistrés dans `register.php` et partagent le même
habillage, la navigation entre rubriques, les README, la coloration, la copie
et les aperçus sticky. `demos-components.php` conserve les exemples des
composants ; `catalogue.php` définit les payloads des autres familles.
Les renderers `card()`, `strate()` et `hero()` sont utilisés selon la famille.
Les strates `pushes*` utilisent les publications News existantes. Les autres
exemples utilisent des données explicites, sans modifier les contenus ACF.
Les README individuels sont affichés pour les composants et les cards.
Aucun README général n’est affiché dans les pages du Styleguide. Les pages Heroes
et Strates affichent uniquement les démos en pleine largeur, sans README
général ni documentation latérale.

Le build publie les README des quatre familles sous
`assets/styleguide/{famille}/{module}/README.html` et leurs README généraux
sous `assets/styleguide/{famille}/README.html`.

La page Styles utilise directement les variables CSS et les classes du thème.
Les valeurs affichées sont lues depuis les styles calculés de `:root` et
actualisées au redimensionnement, notamment pour les espacements responsive.

La section Layout applique réellement `grid-template-columns: var(--layout)`.
Des rectangles occupent les rails `full`, `fluid`, `ctr`, `ctr-sm` et `center`
quand leurs lignes de début et de fin existent dans la définition active.
Le module affiche leur placement et leur largeur en pixels et les actualise
au redimensionnement. Les rails absents restent masqués pour ne pas créer de
colonnes implicites. La démo ne redéfinit pas les variables du layout du thème.

La section Breakpoints lit `assets/styleguide/breakpoints.json`, généré par le
build depuis les seuils en pixels de `assets/styles/breakpoints.scss`. Le
tableau présente les variantes `-up`, `-down` et `-only` disponibles, avec
la plage active et la largeur courante actualisées au redimensionnement.

Les ancres internes du Styleguide défilent avec Lenis quand il est disponible,
sinon avec le scroll natif fluide. Leur position tient compte du sommaire
sticky. L’URL conserve l’ancre et les clics modifiés ou vers un autre onglet
restent natifs. `prefers-reduced-motion` désactive l’animation.

Le sommaire suit la section qui franchit le haut utile de la fenêtre, sous la
navigation sticky. Son lien reçoit `aria-current="location"` et reste visible
dans le sommaire horizontal. Le suivi fonctionne pendant le scroll manuel ou
animé, avec un RAF à la demande et un nettoyage des écouteurs et observers.

Un indicateur fixe en bas à droite de chaque page du Styleguide affiche le
breakpoint courant, sa plage et la largeur du viewport. Il utilise le même
export Sass que le tableau et s’actualise au redimensionnement. Sous le premier
seuil, il indique explicitement « Sous xs » plutôt qu’un breakpoint inexistant.
