# Audit des strates et de l’hydratation — 5 novembre 2026

Périmètre : 22 templates de strates, 21 layouts ACF et la strate statique
`text-animated`, leurs manifestes, styles, trois modules JS propres, les helpers
`strate()`, `options()`, le dispatch ACF et le moteur d’hydratation.
Les composants appelés ont été examinés aux points nécessaires au diagnostic.
Les constats ci-dessous décrivent l’état initial. La passe de correction traite
S1 à S10 : visibilité publique commune aux cards, normalisation des payloads,
chemin d’hydratation Voir plus, imports CSS, dispatch sûr, remplacement de
module, condition média revérifiée, centrage et contraintes des panneaux Snap.

Contrôles ajoutés : `tests/strates-wordpress-contract.php` dans WordPress
(fixtures mémoire, sans écriture de contenu en base) et
`tests/strates-hydration-browser-contract.mjs` dans Chrome. Les contrats PHP
existants couvrent maintenant les strates vides et les layouts mal formés.
Le build de production et l’inventaire SHA-256 passent. Les cinq avertissements
de taille des images de démonstration restent hors du périmètre de correction.

## Priorités

| Réf. | Priorité | Constat |
| --- | --- | --- |
| S1 | P1 | Une sélection manuelle peut exposer un contenu devenu privé |
| S2 | P1 | Le JS « Voir plus » est refusé par l’hydrateur |
| S3 | P2 | CSS de Push et Keynumbers absent du build actuel du starter |
| S4 | P2 | Données mal formées : erreur de dispatch ; quatre strates produisent des warnings à vide |
| S5 | P2 | Changement de module pendant l’import : racine bloquée en pending |
| S6 | P2 | Condition média non revérifiée avant l’initialisation |
| S7 | P2 | Marquee en mode cards ne suit pas la préparation des données des autres remontées |
| S8 | P2 | L’option center de Text Image ne centre pas les deux colonnes |
| S9 | P2 | Une image portrait peut déborder des panneaux Snap |
| S10 | P3 | Documentation et tests ne couvrent pas plusieurs contrats réels |

## S1 — Confidentialité des remontées manuelles

`template-parts/strates/strate-pushes.php` parcourt les IDs sélectionnés et
appelle `theme_post_card_item()`. Ce helper (`inc/front/methods.php:210`)
prépare titre, extrait, image, champs ACF et permalien sans contrôler le statut
public du contenu. La sélection peut rester enregistrée lorsqu’un contenu
précédemment publié devient privé ou brouillon.

Reproduction dans le vrai WordPress, avec un visiteur anonyme et un post privé
synthétique placé uniquement dans le cache mémoire de la requête : la strate
rend son titre et son extrait. Aucun contenu réel n’a été modifié ni publié,
aucune écriture en base n’a été utilisée pour créer cette fixture, et le test
refuse de placer la fixture dans un cache objet persistant.

Les remontées automatiques utilisent bien `post_status => publish` ; ce test
ne démontre pas une fuite sur leurs requêtes ni un accès arbitraire par URL.
Le défaut concerne un ID non public encore présent dans une sélection manuelle.

À faire : filtrer selon la visibilité publique aux points communs de préparation
et de résolution des cards, pas seulement dans une strate. Définir aussi la
politique des contenus protégés par mot de passe et des aperçus. Ne pas se
contenter des droits de l’administrateur courant : une page publique ou mise
en cache ne doit pas dépendre de ses privilèges.

## S2 — Pushes More n’est jamais hydratée avec son attribut actuel

`template-parts/strates/strate-pushes-more.php` produit :

```html
data-module="strates/strate-pushes-more/strate-pushes-more"
```

L’hydrateur accepte exactement une famille et un dossier :

```html
data-module="strates/strate-pushes-more"
```

Le chemin à trois segments est rejeté avant tout import. Reproduction dans
Chrome avec le moteur réel : message `Invalid module name`, aucune hydratation.
Le lien normal peut encore naviguer vers le lot suivant, mais le chargement
AJAX annoncé par le README ne fonctionne pas.

Le contrat PHP existant, `tests/components-contract.php:3141`, attend justement
le mauvais attribut : il passe en masquant le défaut. À corriger avec le markup
et à compléter par un test navigateur de clic et d’ajout réel des cards.

## S3 — Deux styles déclarés ne sont pas importés

`strate-keynumbers.scss` et `strate-push.scss` existent et leurs manifestes
déclarent un CSS normal, mais ils n’ont pas d’import actif dans `assets/app.scss`.
Vérification du CSS compilé par parsing des sélecteurs : ni `.strate-keynumbers`
ni `.strate-push` n’apparaissent dans app/critical, et aucun fichier dédié ne
compense cette absence.

Les deux layouts sont proposés dans ACF. Keynumbers perd notamment sa grille
spécifique ; Push perd sa mise en page desktop. Les composants internes gardent
leurs propres styles, ce qui peut masquer partiellement le problème.

À faire : ajouter ces imports en respectant les modes déclarés. Distinguer
ce défaut du starter du pipeline Forge qui réécrit les imports à partir des
manifestes lors de la génération d’un site ; vérifier les deux chemins.

## S4 — Normalisation PHP incomplète

`template-parts/utils/dispatch-strates.php:14` convertit directement
`acf_fc_layout` en chaîne après avoir seulement vérifié que la ligne est un
tableau. Une valeur tableau produit `Array to string conversion` ; un objet
non Stringable provoque une `Error` non interceptée. Les deux cas ont été
reproduits avec une fixture du dispatcher.

Les appels à vide des templates révèlent aussi :

| Strate | Clés accédées sans défaut |
| --- | --- |
| accordion | text, items |
| intro | text |
| text-image | title, text |
| text | text |

Ces quatre strates peuvent produire des sections vides et des espacements
alors qu’aucun composant utile n’a été rendu.

À faire : valider le type du layout avant conversion, utiliser les valeurs par
défaut de `get_template_args()` ou des accès sûrs, et définir les conditions de
non-rendu. Les noms inconnus mais syntaxiquement valides méritent également
un diagnostic en développement ; `strate()` ne vérifie pas le template avant
d’enregistrer ses assets. Garder le `try/finally` existant qui restaure le
contexte de rendu.

## S5 — Réutilisation d’une racine pendant un import

`assets/modules/hydrate/hydrate.js:368` ignore une racine déjà pending ou loaded
avant de comparer son nouveau `data-module` au précédent. Si l’attribut change
pendant l’import, `isCurrentHydration()` empêche correctement l’ancien module
de démarrer, mais laisse l’élément pending. Même un nouvel appel explicite à
`hydrateElement()` ne lance alors pas le nouveau module.

Reproduction Chrome avec les vrais modules accordion et tooltip et un import
retardé : l’élément reste non hydraté après plusieurs tentatives. Ce cas concerne
une racine réutilisée dynamiquement ; il ne bloque pas chaque chargement de page.

À faire : associer l’état au nom du module et invalider l’ancien état/token
lors d’un changement. Pour un module déjà chargé, nettoyer l’ancienne instance
avant réinitialisation. Définir séparément si les mutations d’attributs doivent
être observées automatiquement ou seulement via une API explicite.

## S6 — Condition média devenue fausse pendant l’attente

`watchMediaCondition()` est appelée avant l’attente de visibilité/import.
`initModule()` ne revérifie pas la media query. Une condition vraie au départ
peut donc devenir fausse avant l’initialisation effective.

Reproduction dans Chrome avec une MediaQueryList contrôlée : après inversion
de `matches` pendant l’attente, le module est quand même marqué hydraté et
initialisé. Les vrais composants sont utilisés pour l’initialisation ; la
condition est simulée pour rendre la course déterministe.

À faire : revérifier la condition au moment de démarrer, puis réarmer l’attente
si nécessaire. Décider et documenter séparément le comportement lorsque la
condition devient fausse après une initialisation déjà terminée.

## S7 — Cards manuelles de Marquee : préparation divergente

En mode cards, Marquee transmet les IDs à `card()` via le composant marquee.
`resolve_card_data()` charge les champs ACF bruts et ajoute `post_id` ; il ne
passe pas par `theme_post_card_item()`. Les groupes `card`, les mappings métier
et les replis titre/extrait/image/permalien WordPress ne sont donc pas préparés
comme dans Pushes ou les remontées automatiques.

Reproduction dans WordPress avec une page publiée synthétique sans champs ACF
plats : Pushes affiche le titre et l’extrait WordPress, Marquee ne les affiche
pas. Avec `card-page`, le rendu peut être vide faute de clé title à la racine.
La même fixture privée n’a pas exposé son titre dans ce chemin : ce résultat
provient ici de données non préparées, pas d’un contrôle de confidentialité.

À faire : harmoniser la préparation des posts dans les chemins manuels, en
traitant S1 d’abord ou conjointement. Réparer les cartes vides sans ajouter le
contrôle de visibilité pourrait étendre le défaut à d’autres remontées.

## S8 — Centrage Text Image appliqué au mauvais niveau

`strate-text-image.scss` applique `align-items: center` à la section `.center`,
alors que les colonnes sont portées par sa grille enfant `.strate-inner`.

Reproduction Chrome avec le PHP et les CSS réels : `align-items` de la grille
interne vaut `normal` ; un titre court reste en haut, aligné sur le haut de
l’image de 374,9 px, au lieu d’être centré verticalement.

À faire : appliquer le centrage au conteneur qui dispose les deux colonnes,
puis vérifier les options center/reverse ensemble et les petits écrans.

## S9 — Hauteur de l’image Snap non contrainte

Dans `strate-snap.scss`, `max-height` et `object-fit: contain` sont appliqués à
`.snap-panel-image`, qui est un `picture`. L’image enfant reste en mode
`fit-fluid`, avec hauteur automatique. `object-fit` sur ce wrapper ne contraint
pas le rendu du `img`.

Reproduction Chrome avec une image portrait existante : wrapper limité à
583 px, image enfant d’environ 999 px. Le panneau lui-même a une hauteur fixe
calculée depuis le viewport. La contribution peut donc déborder du panneau.

À faire : contraindre le bon élément et tester les images portrait, les textes
longs et les faibles hauteurs de fenêtre, sans simplement masquer le contenu.
Le comportement snap doit rester cohérent si la hauteur utile d’un panneau
doit grandir pour accueillir sa contribution.

## S10 — Documentation et couverture

- Le README racine parle encore d’un Intro vide, alors que le PHP rend son
  texte ; il cite `get_fields()` à la place du cache `theme_get_fields()` et
  annonce un défaut de rail fluid tandis que `.strate` utilise désormais ctr.
- Le README de Text liste d’anciens formats WYSIWYG, retirés depuis.
- Le manifeste Marquee annonce un JS eager alors que son markup attend la
  visibilité. Clarifier le rôle de cette métadonnée et aligner les contrats.
- Les tests PHP de cards utilisent des champs ACF title/text plats ; compléter
  avec les groupes card/hero réels et les replis WordPress.
- Les contrats navigateur existants de l’hydratation testent surtout la
  composition des lifecycles. Ajouter imports asynchrones, changements de
  module/media et vrai chargement de Pushes More, sans recopier le défaut
  d’implémentation dans l’assertion.

## Points validés et limites

- 22 templates PHP : syntaxe OK avec le PHP Docker du projet.
- Contrats PHP composants : OK ; syntaxe JS : OK, 34 sources.
- Contrats navigateur existants hydrate, section-snap, scroll-driver et
  split-text : PASS dans Chrome.
- Les 21 layouts ACF et les 22 manifestes correspondent au catalogue ;
  text-animated est volontairement statique et hors ACF, conformément à son
  README. Les composants directement appelés sont déclarés dans les manifestes.
  Les clones et conditions ACF des strates référencent des clés existantes.
- Marquee possède son JS et désactive celui de son composant : pas de double
  propriétaire d’hydratation dans ce chemin. Il transmet pause/resume/destroy.
- Text Animated transmet un lifecycle suspendable et teste reduced-motion.
- Les composants slider et section-snap possèdent l’interactivité des strates
  qui les assemblent ; l’absence de JS propre à ces strates est normale.
- Tests navigateur supplémentaires : ajout/hydratation d’un accordéon, maintien
  de l’instance après déplacement DOM, nettoyage des listeners après retrait,
  et absence d’initialisation après suppression pendant l’import : OK.
- Le chargement des modules est mutualisé, les noms sont limités aux familles
  autorisées et les imports en échec sont journalisés. Les noms de strates ne
  permettent pas de traversée de répertoires ; les anciens slugs documentés
  restent remappés.
- Les remontées automatiques bornent le nombre d’items, demandent des posts
  publiés, évitent le comptage total et préchargent posts/métadonnées. Le JS
  Pushes More prévoit l’annulation des requêtes à la destruction ; son
  chargement est actuellement bloqué par S2.

Les défauts de sécurité et de données ont été testés avec des objets
synthétiques en mémoire, sans création de posts en base. Les défauts CSS et
d’hydratation ont été reproduits dans des pages locales isolées. Aucun build,
changement de contribution ou démarrage des services arrêtés n’était requis.
Pas de mesure Lighthouse exhaustive ni de simulation de tous les réseaux,
viewports ou combinaisons de strates. Les dépendances dynamiques aux cards
doivent aussi être vérifiées lors de la génération Forge selon les CPT choisis.

Ordre conseillé : S1/S2, puis S3/S4/S7, ensuite S5/S6/S8/S9 et la couverture
documentaire et de tests S10.
