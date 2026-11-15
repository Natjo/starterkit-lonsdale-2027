# Interactions du design system Forge

## Documentation des composants

Chaque section `component-*` présente deux colonnes : le titre et les aperçus
à gauche, le README rendu en HTML à droite. Sous 900 px, les deux colonnes
s’empilent. La même disposition est utilisée pour les quatre familles.

Le renderer embarqué de Forge convertit les README des quatre familles avec MarkdownIt et génère
`wp-content/mu-plugins/forge-design-system/docs/{famille}/{slug}/README.html` dans le thème. Forge reconstruit ces documents quand le commit du catalogue ou les ressources embarquées changent. Les fichiers générés
ne doivent pas être édités manuellement.

`readme.php` lit uniquement ces fichiers locaux et filtre le HTML avec
`wp_kses_post()`. Les titres Markdown sont décalés à partir du niveau 4 ;
les tableaux, listes et blocs de code sont rendus. Les liens vers un autre
README de composant pointent vers sa section du Design system.

La coloration syntaxique des blocs de code est générée au build avec
`highlight.js`, selon la langue de la clôture Markdown (`php`, `js`, `scss`,
`html`, etc.). Les langues absentes ou inconnues restent en texte échappé.
Les couleurs sont définies dans `design-system.css` et limitées aux README ;
aucun script de coloration n’est chargé dans le navigateur.

## Copier les exemples

Le module `src-tauri/design-system/design-system.js`, hydraté uniquement sur le
Design system sur `[data-forge-design-system]`, ajoute une icône de copie à
chaque bloc de code. Il copie `code.textContent` avec l’API Clipboard, affiche
un état de succès ou d’erreur et annonce le résultat via `role="status"`.
Sans API Clipboard, aucun bouton n’est ajouté. Le module nettoie ses écouteurs,
états temporaires et éléments injectés lors de sa destruction.

Ce dossier JavaScript de page est réservé au modèle interne du Design system ;
il n’ajoute pas d’entrée au catalogue Forge.

Sur desktop, les `.design-system-demo` restent sticky sous le sommaire. Le module
ajuste leur décalage selon la hauteur du sommaire via `ResizeObserver`, avec
un espace de 32 px sous la navigation.
Sous 900 px, les démos reprennent leur position normale.

## Pages du catalogue

| URL locale | Modèle | Contenu |
| --- | --- | --- |
| `/styles/` | `styles.php` | Couleurs, typographie, espacements, surfaces et layout |
| `/components/` | `design-system.php` | Composants |
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
Les exemples des pages Strates et Heroes occupent toute la largeur du viewport,
sans cadre ni padding de prévisualisation, avec une bordure supérieure. Ils
héritent de la grille réelle du site et conservent leurs conteneurs internes.
Dans la page Strates, les boutons « Options » et « README » sont placés à côté
du titre, avec un aspect d’onglets. Le bouton actif partage le fond du panneau,
auquel il est collé sans séparation visuelle. Le panneau est superposé à
l’exemple, sans déplacer la strate. Un seul panneau est ouvert à la fois. Un second clic,
Échap ou un clic extérieur le ferme ; Échap rend le focus au
bouton d’ouverture. Les panneaux restent non modaux pour tester l’exemple.
Le titre et les boutons sont centrés sur la même ligne. Leur défilement interne
reste natif grâce à `data-lenis-prevent`, sans arrêter Lenis sur la page.
La hauteur maximale du README est calculée à chaque ouverture, jusqu’à 16 px
du bas du viewport, puis reste fixe jusqu’à sa fermeture. Aucun recalcul n’est
effectué au scroll ou au redimensionnement. Le rendu
Markdown et la copie des blocs de code restent disponibles.
Aucun README général n’est affiché dans les pages du Design system. La page Heroes
ne comporte pas de documentation latérale.

Le build publie les README des quatre familles sous
`wp-content/mu-plugins/forge-design-system/docs/{famille}/{module}/README.html`.

La page Styles utilise directement les variables CSS et les classes du thème.
Les valeurs affichées sont lues depuis les styles calculés de `:root` et
actualisées au redimensionnement, notamment pour les espacements responsive.

La section Layout applique réellement `grid-template-columns: var(--layout)`.
Des rectangles occupent les rails `full`, `fluid`, `ctr`, `ctr-sm` et `center`
quand leurs lignes de début et de fin existent dans la définition active.
Le module affiche leur placement et leur largeur en pixels et les actualise
au redimensionnement. Les rails absents restent masqués pour ne pas créer de
colonnes implicites. La démo ne redéfinit pas les variables du layout du thème.

La section Breakpoints lit `docs/breakpoints.json`, généré par le renderer de Forge depuis les seuils en pixels de `assets/styles/breakpoints.scss`. Le
tableau présente les variantes `-up`, `-down` et `-only` disponibles, avec
la plage active et la largeur courante actualisées au redimensionnement.
Chaque breakpoint possède un fond distinct, appliqué uniquement à sa ligne active
et partagé avec l’indicateur fixe :
rose (`xs`), orange (`sm`), jaune (`md`), vert (`lg`), turquoise (`xlg`), bleu
(`wd`), violet (`xwd`) et mauve (`xlgwd`). La palette est définie une seule fois
via `data-breakpoint` dans `design-system.css`. La ligne active est colorée sans
contour supplémentaire. Les fonds de la ligne active et de l’indicateur sont opaques à 40 %, avec un
texte opaque. Seul l’indicateur utilise un flou d’arrière-plan de 6 px. L’état sous le premier seuil utilise un fond gris.

Les ancres internes du Design system défilent avec Lenis quand il est disponible,
sinon avec le scroll natif fluide. Leur position tient compte du sommaire
sticky. L’URL conserve l’ancre et les clics modifiés ou vers un autre onglet
restent natifs. `prefers-reduced-motion` désactive l’animation.

Le sommaire suit la section qui franchit le haut utile de la fenêtre, sous la
navigation sticky. Son lien reçoit `aria-current="location"` et reste visible
dans le sommaire horizontal. Le suivi fonctionne pendant le scroll manuel ou
animé, avec un RAF à la demande et un nettoyage des écouteurs et observers.

Un indicateur fixe en bas à droite de chaque page du Design system affiche le
breakpoint courant, sa plage et la largeur du viewport. Il utilise le même
export Sass que le tableau et s’actualise au redimensionnement. Sous le premier
seuil, il indique explicitement « Sous xs » plutôt qu’un breakpoint inexistant.

Le sommaire horizontal masque sa scrollbar. Il reste défilable au tactile et
au trackpad, ainsi que par glisser à la souris. Un glissement ne déclenche pas
le lien au relâchement ; les clics simples et la navigation clavier restent actifs.
Un fondu de 2,4 rem apparaît sur chaque bord où des liens restent hors champ ;
il disparaît lorsque le début ou la fin de la liste est atteint.

Sur les pages Styles, Composants, Cards, Strates et Heroes, une recherche à gauche
du sommaire filtre les liens
et les sections par nom, sans tenir compte de la casse ou des accents. Les mots
saisis doivent tous correspondre. Vider la recherche réaffiche le catalogue ;
un message apparaît si aucun exemple ne correspond. Sur mobile, le champ
passe au-dessus de la liste défilante.

La recherche est conservée dans le paramètre d’URL `design_system_search` et l’ancre
du lien sélectionné reste dans l’URL. Au rechargement, le filtre est restauré
avant de repositionner l’exemple sous le sommaire, après le chargement de la
page et des polices. Une interaction de l’utilisateur annule ce repositionnement
pour éviter de le ramener en arrière. Aucune donnée de recherche n’est mise en
cache dans le navigateur.

Le panneau « Options » de chaque strate est organisé en trois blocs empilés :
Conteneur, Marges et Background (activation, couleur et paddings). Le nom d’ancre
n’est pas proposé et les identifiants existants restent inchangés. Les choix et
valeurs initiales proviennent de `acf-json/group_strates_options.json`, avec
priorité aux options explicites de la démo. Le JavaScript applique les classes
communes de `options()` à la racine de l’exemple, sans remplacer le HTML ni
réinitialiser ses modules. Les réglages du fond sont désactivés lorsque le fond
est inactif. « Réinitialiser » restaure les classes d’origine.
Les changements sont temporaires, propres à chaque exemple et ne sont ni
enregistrés dans WordPress ni conservés au rechargement. Un exemple sans rendu
(par exemple sans News publiées) garde ses contrôles désactivés.
