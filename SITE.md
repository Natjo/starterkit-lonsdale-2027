# Site actuel — Starter kit 2027

Ce document décrit l'état fonctionnel et éditorial actuellement observable du
site. Il a été établi à partir du dépôt, des fichiers ACF JSON et d'une lecture
seule de la configuration WordPress locale le 22 juillet 2026.

Il complète `AGENTS.md` : ce fichier décrit **ce qui existe dans le site**,
tandis que `AGENTS.md` et les README du thème décrivent **comment le modifier**.

Les fonctionnalités présentes dans le code mais non raccordées à WordPress ou
à ACF sont signalées explicitement. Elles ne doivent pas être considérées comme
des besoins métier validés.

## 1. Identité et objectif actuel

| Information | État actuel |
| --- | --- |
| Nom WordPress | `Starter kit 2027` |
| Nom du thème | `STARTERKIT LONSDALE 2027` |
| Slug du thème | `starterkit-lonsdale-2027` |
| URL locale | `https://starterkit-lonsdale-2027.code` |
| Langue WordPress | `fr_FR` |
| Multisite | Non prévu dans la configuration actuelle |
| Vocation constatée | Starter WordPress et styleguide servant à développer, tester et documenter des composants, cards, heroes et strates |
| Public actuel | Équipe de développement et d'intégration |

Aucun brief métier, domaine de production, public final, contenu définitif ou
maquette de site n'est versionné dans le dépôt. Le site actuel doit donc être
considéré comme un socle technique, pas comme un site éditorial finalisé.

## 2. Sources de vérité

| Sujet | Source |
| --- | --- |
| Règles générales | `AGENTS.md` |
| Composants | `assets/components/README.md` et README locaux |
| Cards | `assets/cards/README.md` et README locaux |
| Common | `assets/common/README.md` et README locaux |
| Strates et grille | `assets/strates/README.md` |
| Groupes ACF | `web/wp-content/themes/starterkit-lonsdale-2027/acf-json/` |
| État des pages et réglages | Base WordPress locale |

`SITE-sample.md` reste le modèle vierge à copier et remplir pour un futur site
client. Le présent `SITE.md` est uniquement la photographie du starter actuel.

## 3. Périmètre réellement en place

Le starter contient actuellement :

- une page d'accueil construite avec un hero ACF et un flexible content ACF ;
- une page de styleguide dédiée aux composants ;
- deux heroes PHP et leurs groupes ACF ;
- dix layouts disponibles dans le flexible content ;
- des templates de strates supplémentaires, dont certains sont fonctionnels et
  d'autres encore vides ;
- une card `news` ;
- un Custom Post Type interne `news` et une API d'import ;
- deux emplacements de menus enregistrés, sans menu affecté ;
- une page ACF Options contenant le token de l’API News ;
- les rôles WordPress natifs, sans rôle personnalisé déclaré dans le thème.

## 4. Extensions actives localement

| Extension | État |
| --- | --- |
| Advanced Custom Fields Pro | Active |
| Classic Editor | Active |
| Regenerate Thumbnails | Active |

WPML n'est pas actif dans l'environnement local. Le helper `getLanguages()` et
quelques nettoyages CSS conservent toutefois une compatibilité technique avec
WPML. Cela ne constitue pas une exigence multilingue validée.

## 5. Pages et templates WordPress

### Pages présentes en base

| Page | ID | URL ou slug | Statut | Template | Comportement actuel |
| --- | ---: | --- | --- | --- | --- |
| Homepage | 2 | `/` ; slug stocké `page-d-exemple` | Publiée | Défaut, résolu par `front-page.php` | Hero `homepage`, puis flexible content `strates` |
| Styleguide | 118 | `/components/` | Publiée | `template-styleguide.php` | Affiche le styleguide des composants |
| Politique de confidentialité | 3 | `/politique-de-confidentialite/` | Brouillon | Défaut | Passe actuellement par le template générique incomplet `page.php` |

La lecture statique est configurée dans WordPress : `show_on_front = page` et
`page_on_front = 2`.

### Comportement des templates

| Template | État actuel |
| --- | --- |
| `front-page.php` | Fonctionnel : common `header-nav`, hero ACF `homepage`, dispatch des strates, footer |
| `template-styleguide.php` | Fonctionnel pour la démonstration des composants |
| `page.php` | Générique : hero ACF avec titre WordPress en fallback, contenu natif éventuel et dispatch des strates |
| `index.php` | Liste générique avec extraits et pagination |
| `single.php` | Titre et contenu du post |
| `archive.php` | Titre d’archive, liste et pagination |
| `taxonomy.php` | Titre de taxonomie, liste et pagination |
| `search.php` | Résultats regroupés par CPT, sidebar d’ancres et pagination |
| `404.php` | Message générique et retour vers l’accueil |

Les templates placeholders ne décrivent pas encore une arborescence métier à
reproduire. Leur contenu doit être défini pour chaque futur site.

La recherche serveur inclut les Pages et tous les CPT publics personnalisés,
mais exclut les articles natifs masqués par le starter. Les résultats de la
page courante sont regroupés par type. News utilise `card-news`, Page utilise
`card-page` et un futur CPT utilise sa card homonyme lorsqu’elle existe, sinon
`card-page`.

## 6. Contenus WordPress

### Pages

Les pages sont utilisées. L'éditeur de contenu WordPress natif est retiré pour
le type `page` ; le contenu éditorial doit donc venir des champs ACF prévus.

À ce jour, seul le front page possède un flexible content `strates`. Les pages
internes ont un groupe `Hero - Page`, mais `page.php` ne le charge pas encore
depuis ACF.

### Articles natifs

Le menu d'administration des articles est masqué. L'éditeur et l'extrait natifs
du type `post` sont retirés. Un article publié est présent dans la base locale,
mais aucun parcours front finalisé ne l'exploite.

Les articles natifs ne font donc pas partie d'un besoin métier confirmé.

### Custom Post Type `news`

| Propriété | Valeur actuelle |
| --- | --- |
| Clé | `news` |
| Libellé | `News` |
| Interface d'administration | Oui |
| Public | Oui |
| Page individuelle | Oui, `/news/{slug}/` |
| Archive | Oui, `/news/` |
| REST WordPress | Oui |
| Supports | `title`, `thumbnail`, `excerpt` |
| Icône | `dashicons-rss` |
| Contenus locaux | Aucun contenu publié |

## 7. Taxonomies

Aucune taxonomie personnalisée n'est enregistrée par le thème actuel.

## 8. Menus et éléments globaux

### Emplacements enregistrés

| Emplacement | Libellé administrateur | Affectation locale |
| --- | --- | --- |
| `menu-header` | Menu Principal | Aucun menu affecté |
| `menu-footer` | Menu Footer | Aucun menu affecté |

Le common `header-nav` et le footer utilisent `wp_nav_menu()`. Le header
n’hydrate son bouton mobile que lorsqu’un menu est affecté. Sans menu, le
header conserve uniquement le logo et le footer son copyright générique.

### Page d'options

Une sous-page ACF `Options` est enregistrée. Aucun champ d'options n'est présent
dans la base locale au moment de l'audit.

Le groupe JSON `(Strates) Paramètres` existe, mais il est inactif et son groupe
`options` ne contient actuellement aucun sous-champ. Le helper PHP `options()`
accepte néanmoins déjà la structure technique décrite dans le README des
strates : conteneur, marges, fond et paddings.

Les anciennes clés `menu_name` et `is_lonsdale` ne font pas partie du contrat
actuel et ne doivent pas être réintroduites.

## 9. Conventions ACF observées

- Les groupes JSON sont enregistrés dans
  `web/wp-content/themes/starterkit-lonsdale-2027/acf-json/`.
- Les images présentes dans les groupes actuels retournent un ID WordPress.
- Tous les champs actuellement déclarés sont facultatifs.
- Les WYSIWYG de texte utilisent généralement la toolbar `full` sans upload de
  média.
- Les WYSIWYG légers utilisent la toolbar `light` sans upload de média.
- Aucun groupe n'est exposé dans la REST API ACF (`show_in_rest = 0`).
- Le layout `pushes` emploie un champ relation pour sa sélection manuelle.

## 10. Groupes ACF versionnés

| Groupe | État | Emplacement | Rôle |
| --- | --- | --- | --- |
| `Strates` | Actif | Page d'accueil et CPT `news` | Flexible content `strates` |
| `(Strates) Paramètres` | Inactif | Ancienne règle sur les articles | Groupe cloné par `text_image`, actuellement vide |
| `Hero - Homepage` | Actif | Page par défaut utilisée comme page d'accueil | Groupe `hero-homepage` |
| `Hero - Page` | Actif | Pages différentes de la page d'accueil | Groupe `hero-page` |
| `Hero - News` | Actif | CPT `news` | Groupe `hero-news` |

## 11. Heroes

### Hero `homepage`

Groupe ACF : `hero-homepage`, facultatif et limité à la page d'accueil.

| Label | Nom ACF | Type | Obligatoire | Format |
| --- | --- | --- | --- | --- |
| Surtitre | `suptitle` | WYSIWYG léger | Non | HTML filtré attendu |
| Titre | `title` | Textarea | Non | Retours convertis en `<br>` par ACF |
| Texte | `text` | WYSIWYG | Non | HTML |
| Images | `items` | Répéteur | Non | Sans limite définie |
| Image d'un item | `images` | Image | Non | ID WordPress |

Le template PHP utilise `suptitle`, `title` et `text`. Il n’exploite pas encore
le répéteur `items`, dont le comportement visuel reste à définir pour le futur
site.

### Hero `page`

Groupe ACF : `hero-page`, facultatif et proposé aux pages autres que l'accueil.

| Label | Nom ACF | Type | Obligatoire | Format |
| --- | --- | --- | --- | --- |
| Titre | `title` | Textarea | Non | Retours convertis en `<br>` par ACF |
| Texte | `text` | WYSIWYG | Non | HTML |

Le template utilise `title` et `text`. `page.php` transmet les valeurs du groupe
ACF et utilise le titre WordPress lorsque le titre ACF est vide.

## 12. Flexible content `strates`

Le groupe `Strates` est actif sur la page d'accueil et le CPT `news`. Il
contient un flexible content facultatif, sans minimum ni maximum, dont le
bouton est `Ajouter une strate`.

Le flexible content propose dix layouts : `text_image`, `text`, `intro`,
`media`, `quote`, `pushes`, `slider`, `marquee`, `keynumbers` et `accordion`.

Trois d’entre eux sont actuellement utilisés dans cet ordre sur la page
d'accueil locale :

1. `text_image` ;
2. `text` ;
3. `accordion`.

La correspondance technique est :

```text
acf_fc_layout = text_image
template       = template-parts/strates/strate-text_image.php
appel          = strate('text_image', $strate)
```

### Layout `text_image`

Rôle constaté : afficher un titre, un texte et une image.

| Label | Nom ACF | Type | Obligatoire | Format |
| --- | --- | --- | --- | --- |
| Titre | `title` | WYSIWYG léger | Non | HTML filtré |
| Texte | `text` | WYSIWYG | Non | HTML |
| Image | `image` | Image | Non | ID WordPress |
| Paramètres | `settings` | Clone seamless | Non | Clone de `(Strates) Paramètres` |

Le clone ne produit actuellement aucune option, car le groupe cloné est vide.
Le template réutilise `title`, `text` et `picture`.

### Layout `text`

Rôle constaté : afficher un titre et un texte éditorial.

| Label | Nom ACF | Type | Obligatoire | Format |
| --- | --- | --- | --- | --- |
| Titre | `title` | Texte | Non | Texte simple |
| Texte | `text` | WYSIWYG | Non | HTML |

Le template réutilise les composants `title` et `text`. Ce layout ne clone pas
actuellement les options communes, même si son template accepte un tableau
`options` lorsqu'il lui est transmis par code.

### Layout `accordion`

Rôle constaté : afficher une liste d'éléments dépliables, avec une seule entrée
ouverte à la fois.

| Label | Nom ACF | Type | Obligatoire | Format |
| --- | --- | --- | --- | --- |
| Item | `items` | Répéteur | Non | Sans minimum ni maximum |
| Titre | `title` | Texte | Non | Sous-champ du répéteur |
| Texte | `text` | WYSIWYG | Non | Sous-champ du répéteur |

Le template force `multiple` à `false`. La gestion du mode multiple reste une
responsabilité de l'appel PHP du composant et non un champ ACF du layout.

## 13. Inventaire des templates de strates

| Strate | Dans ACF | État PHP actuel | Assets propres | Statut fonctionnel |
| --- | --- | --- | --- | --- |
| `text_image` | Oui | Rend `title`, `text`, `picture` | SCSS | Fonctionnelle |
| `text` | Oui | Rend `title` et `text` | Aucun asset propre | Fonctionnelle |
| `accordion` | Oui | Rend le composant `accordion` | Aucun asset propre | Fonctionnelle via le composant |
| `slider` | Oui | Slider de cards ou d’images | SCSS et JS | Fonctionnelle et administrable |
| `marquee` | Oui | Marquee de cards ou d’images | SCSS et JS | Fonctionnelle et administrable |
| `formulaire` | Non | Formulaire statique de démonstration | SCSS | Réservée au styleguide |
| `textAnimated` | Non | Titre statique animé au scroll | SCSS et JS | Réservée au styleguide |
| `paralax` | Non | Image plein écran avec titre centré et parallaxe | SCSS et JS | Réservée au styleguide |
| `intro` | Oui | Rend le composant `intro` | Aucun asset propre | Fonctionnelle |
| `keynumbers` | Oui | Liste de chiffres clés | SCSS | Fonctionnelle et administrable |
| `media` | Oui | Rend un média image ou vidéo | Aucun asset propre | Fonctionnelle |
| `pushes` | Oui | Sélection manuelle ou remontée automatique de cards | Aucun asset propre | Fonctionnelle |
| `quote` | Oui | Rend une citation et son image facultative | Aucun asset propre | Fonctionnelle |

Une IA ne doit pas ajouter automatiquement au flexible ACF les strates qui ne
sont pas encore administrables. Leur contrat métier et leurs champs doivent
d'abord être définis.

### Cycle de vie pour un futur site

Cet inventaire est un catalogue de départ. Les strates fréquemment utiles
peuvent être reprises telles quelles, adaptées ou renommées pour correspondre
au vocabulaire métier du futur site. Les strates qui ne servent pas au projet
pourront être supprimées une fois le catalogue final validé.

Aucune strate ne doit être supprimée ou renommée pendant l'audit initial. Avant
un nettoyage, vérifier ses références dans ACF, les templates PHP, les assets
SCSS et JavaScript, les attributs `data-module`, le styleguide et les README.
Un renommage doit conserver la correspondance complète entre
`acf_fc_layout`, `strate('nom')`, le dossier `strate-<nom>` et ses assets.

## 14. Common, cards et formulaire

### Common

- `header-nav` : logo, menu WordPress et bouton mobile hydraté avec nettoyage.
- `footer` : titre facultatif, menu WordPress et copyright générique ; aucune
  dépendance JavaScript.

### Cards

Une seule card est conservée : `card-news`. Le renderer accepte un ID de post,
un payload personnalisé, ou un ID avec surcharges. Son contrat détaillé est
documenté dans `assets/cards/card-news/README.md`.

La strate `pushes` alimente cette card avec une sélection manuelle de News ou
une remontée automatique des News les plus récentes. Elle accepte aussi un
titre, un texte et un lien interne ou externe.

### Formulaire

La strate `formulaire` est une démonstration statique de tous les types du
composant `field`. Elle n'accepte aucun contenu ACF et n'envoie actuellement les
données vers aucun traitement métier.

## 15. Administration et sécurité

- Les commentaires et trackbacks sont désactivés pour tous les contenus.
- L'éditeur natif est retiré des pages, articles et News ; le contenu
  éditorial des News vient du flexible content ACF `strates`.
- Les flux RSS sont désactivés.
- XML-RPC est désactivé.
- L'API REST anonyme est bloquée par `rest_pre_dispatch`, sauf pour la route
  exacte `/starter/v1/maintenance`.
- L'upload SVG est réservé aux administrateurs et les fichiers sont nettoyés.
- La barre d'administration est masquée sur le front.
- Aucun rôle ou droit personnalisé n'est déclaré.
- Une route de maintenance protégée existe sous
  `GET /starter/v1/maintenance`.

## 16. Données et médias

| Élément | État local constaté |
| --- | --- |
| Page d'accueil affectée | Oui, page 2 |
| Pages de démonstration | Homepage et Styleguide publiées |
| Menus affectés | Non |
| Options ACF renseignées | Non |
| `news` publiées | Aucune |
| Images ACF | Format ID |

Les tailles d'images personnalisées encore enregistrées sont `880x0`, `320x0`,
`378x424` et `34x0`. Leurs commentaires font référence à d'anciens modules ;
leur utilité doit être confirmée avant la finalisation du starter.

## 17. Écarts connus avant finalisation

Ces points sont constatés mais ne sont pas corrigés par ce document :

1. le hero homepage n'utilise pas encore son répéteur `items` ;
2. le groupe cloné `(Strates) Paramètres` est inactif et son groupe `options`
   est vide ;
3. la documentation locale des strates est encore incomplète, à l'exception
    de la strate statique `formulaire`.

Ces écarts doivent être arbitrés avant d'utiliser cet état comme modèle exact
pour un nouveau site.

## 18. Ce qui reste à définir pour un site client

Les informations suivantes ne peuvent pas être déduites du starter :

- objectif, public et domaine du site final ;
- maquettes et arborescence définitive ;
- langues et solution multilingue ;
- CPT, taxonomies et relations métier à conserver ou créer ;
- champs globaux du header, du footer et des options ;
- strates réellement nécessaires et pages sur lesquelles elles sont permises ;
- champs, variantes et règles de validation des nouvelles strates ;
- fonctionnement des listings, recherches et formulaires ;
- rôles éditoriaux particuliers ;
- données initiales à créer dans WordPress.

Pour un nouveau projet, copier `SITE-sample.md` vers `SITE.md`, compléter ces
informations, puis faire valider le modèle de données avant l'implémentation.

## Prompt pour reproduire uniquement le back actuel

```text
Lis AGENTS.md, SITE.md et les README du thème.

Reproduis uniquement l'architecture WordPress et ACF marquée comme fonctionnelle
dans SITE.md : réglages du thème, pages nécessaires, heroes, flexible content,
layouts ACF et templates PHP associés. Ne transforme pas les squelettes, les
placeholders ou les compatibilités dormantes en besoins métier.

Avant toute modification, sépare dans ton audit :
1. ce qui est fonctionnel et doit être conservé ;
2. ce qui est présent mais non raccordé ;
3. ce qui nécessite une décision métier.

Ne corrige pas implicitement les écarts connus. Propose leur correction et
attends une validation lorsqu'elle change un contrat ACF ou l'architecture du
site. Ne réalise pas le design final, les animations ou le JavaScript front et
ne touche pas à la base de données sans autorisation explicite.
```
