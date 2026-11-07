# Audit des heroes — 5 novembre 2026

Audit du catalogue présent dans le dépôt : `homepage`, `page`, `news`,
`flexible`. Templates PHP, composants appelés, champs ACF, manifestes,
styles, chargement des médias, helpers de rendu et tests ont été examinés.
Aucune correction fonctionnelle n’est appliquée dans ce rapport.

## Constats

### H1 — P1 : le hero Flexible n’a pas de champ ACF dans le dépôt

[`template-flexible.php`](../../web/wp-content/themes/starterkit-lonsdale-2027/template-flexible.php)
appelle `hero('flexible')` sans payload. Le helper cherche donc le champ
`hero-flexible`. Le manifeste existe, mais aucun groupe JSON ACF du dépôt ne
déclare ce champ, et aucun README local ne décrit sa contribution.

Sur une installation issue du dépôt, sans configuration additionnelle en base,
ce template ne dispose pas de données à transmettre au titre. Le rendu avec
un payload vide confirme un header vide, sans `h1`.

À faire : définir le groupe `hero-flexible`, sa localisation et son contrat,
ou choisir explicitement une autre source de données dans le template. Ne pas
réintroduire une activation implicite d’un autre hero pour compenser.

### H2 — P2 : les singles CPT ignorent la contribution de Hero Page

`single-project.php`, `single-team.php`, `single-expertise.php` et
`single-testimonial.php`, ligne 7, appellent tous :

```php
hero('page', ['title' => get_the_title()]);
```

Le payload explicite remplace entièrement les données ACF, conformément au
contrat du helper. Les champs `hero-page.title` et `hero-page.text` peuvent
donc apparaître dans le BO sans être rendus sur ces pages. Modifier le texte
du hero dans le BO n’aura aucun effet avec ces appels.

À faire : décider du contrat de ces singles. Si le hero est contributif,
utiliser ses données ACF et définir précisément le repli vers le titre natif
en cas de valeur absente. Si le titre doit rester natif, ne pas présenter des
champs éditoriaux inutilisés sur ces écrans. Préserver la distinction entre
champ absent et champ volontairement vidé.

### H3 — P2 : la localisation de Hero Page est trop large

`acf-json/group_hero_page.json:66` utilise seulement
`page_type != front_page`. Cette règle ne limite ni le type de publication
ni le modèle. Le code de la règle ACF compare effectivement l’ID à celui de
l’accueil ; un contenu d’un autre type peut donc satisfaire cette inégalité.

Elle peut ainsi afficher Hero Page sur les news, déjà dotées de Hero News,
sur Flexible, qui rend un autre hero, et sur les CPT de H2. Son titre étant
obligatoire, une contribution inutile peut aussi être imposée dans le BO.

À faire : aligner les règles sur les templates qui rendent réellement ce hero.
Ne pas restreindre arbitrairement à `page` : les singles CPT doivent aussi être
traités selon le contrat retenu en H2.

### H4 — P2 : l’image de Hero News est chargée en lazy

`template-parts/heros/hero-news.php:17` appelle `picture` sans option de
chargement critique. Le rendu dans WordPress confirme `loading="lazy"`, sans
`fetchpriority="high"`. L’image est placée immédiatement après le titre :
lorsqu’elle se trouve au premier écran et devient LCP, ce choix retarde son
chargement.

À faire : définir l’image principale comme critique/préchargée pour ce cas,
en conservant une seule image principale préchargée. Le hero Homepage utilise
déjà correctement `preload => true`.

### H5 — P2 : Homepage peut chevaucher la section suivante

`hero-homepage.scss:2` impose `height: 100vh`. Le contenu est centré dans cette
hauteur fixe, sans mécanisme lui permettant d’agrandir le hero.

Reproduction dans Chrome avec les CSS compilés, un titre long et plusieurs
paragraphes : fenêtre de hauteur utile 553 px, hero de 553 px, bas du contenu
à 1 600 px et début de la section suivante à 553 px. Le chevauchement est réel.

À faire : privilégier une hauteur minimale avec croissance du contenu, puis
valider le cadrage de l’image et les petites hauteurs d’écran. Ne pas résoudre
le problème en masquant le débordement : cela couperait la contribution.

### H6 — P3 : les images utilisent leur taille originale par défaut

Homepage et News ne précisent ni `size`, ni `desktop_size`/`mobile_size`.
Le composant `picture` utilise donc `full`. Les variantes desktop/mobile sont
bien prises en charge, mais elles ne suffisent pas à limiter la résolution
d’une photo originale très grande.

Les placeholders actuels de Homepage pèsent environ 476,7 KiB sur desktop et
407,2 KiB sur mobile, avant éventuelle conversion WebP du site.

À faire : choisir des tailles WordPress adaptées au design et vérifier les
URL effectivement sélectionnées, leurs dimensions et les preloads. La
compression des placeholders est une optimisation distincte du PHP.

### H7 — P3 : le prescan peut charger du CSS de heroes non rendus

`inc/front/methods.php:638` parcourt tous les champs `hero-*` non vides.
`functions.php:165` appelle ce prescan avant le rendu des pages singulières.
Il ne vérifie pas quel hero le template va réellement afficher.

Des données d’un ancien modèle peuvent rester enregistrées après un changement
de template. Elles suffisent alors à faire inclure son CSS critique. Un test
isolé confirme que deux groupes non vides font enregistrer les deux CSS.

À faire : enregistrer uniquement le hero effectivement rendu, ou exploiter
son enregistrement au rendu et l’injection tardive déjà fournie par le buffer.
Éviter d’analyser les fichiers PHP à chaque requête pour résoudre ce point.

### H8 — P3 : documentation et couverture des heroes à compléter

- Flexible manque de README et n’apparaît pas dans le tableau du README racine.
- Homepage utilise `picture`, mais ne le liste pas dans son README local.
  Son manifeste, lui, déclare correctement cette dépendance.
- Les tests existants couvrent les composants, les médias et le registre CSS,
  ainsi que les defaults du manifeste Homepage. Ils ne rendent pas directement
  les quatre heroes avec leurs cas ACF, vides, invalides et explicites.

À faire : compléter les contrats locaux et ajouter des tests ciblés sur les
défauts corrigés, notamment Flexible, les singles contributifs et le LCP News.

## Limite supplémentaire de l’API CSS

Les heroes actuels ayant du CSS déclarent tous `loading: critical`, conformément
au README. Ce chemin fonctionne et est cohérent avec `app.scss`.

En revanche, `hero()` enregistre uniquement le chemin critique : un import de
hero passé en `once` produirait un fichier sous `css/once/heros`, que ce helper
ne charge pas. Ce cas a été reproduit dans un dossier temporaire. Ce n’est pas
une panne du catalogue actuel, mais une limite à traiter ou à interdire
explicitement avant de proposer ce mode pour les heroes dans Forge.

## Points validés

- Les noms sont filtrés et les chemins de template ne permettent pas de
  traversée avec `../`. Un nom invalide ne produit rien.
- Les quatre templates utilisent les composants procéduraux actuels.
- Les manifestes déclarent les composants réellement appelés. Les deux SCSS
  présents sont correctement importés en critical.
- Aucun des quatre heroes ne possède de JavaScript propre et aucun manifeste
  n’en annonce. Il n’y a donc pas de listener, RAF ou module d’hydratation
  manquant à corriger dans ce catalogue.
- Des payloads mal formés et du HTML hostile ont été rendus dans WordPress :
  pas de balise script, d’attribut `onerror` ni d’URL `javascript:` conservés
  dans les sorties testées ; le contexte parent est restauré pour les quatre
  heroes. Cela ne remplace pas un audit général de tous les composants.
- Homepage rend bien son image sans lazy-loading et avec priorité haute.
- Le format ACF de date de News (`d/m/Y`) est reconnu par le composant `date`.
- L’absence de fallback PHP vers les defaults des manifestes est conforme au
  contrat : ces defaults préremplissent Forge, sans remplacer les contributions
  volontairement vidées dans WordPress.

## Vérifications et limites

Syntaxe PHP des quatre heroes : OK avec le PHP Docker du projet. Contrats
composants et assets : OK sur l’hôte. Le test assets remonte une dépréciation de
`ReflectionProperty::setAccessible()` sous PHP 8.5, sans échec du contrat.
Rendu réel des heroes et vérifications de filtrage : OK via WP-CLI dans des
conteneurs temporaires, sans enregistrement de contenu. Débordement Homepage :
reproduit dans Chrome avec les styles compilés. CSS prescan/once : reproduits
dans un dossier temporaire.

Les services arrêtés du site n’ont pas été démarrés. Les règles ACF ont été
vérifiées depuis les JSON et l’implémentation locale du plugin ; les données
spécifiques déjà présentes dans la base ne sont pas considérées comme le
contrat reproductible du starter. Pas de mesure Lighthouse réseau sur chaque
page, ni de modification des templates, styles ou champs dans cet audit.
