# Configuration SEO

## Pages d’options ACF

L’entrée parente « Options » redirige vers la première sous-page configurée et
n’apparaît pas une seconde fois dans son sous-menu. Seules les pages d’options
réelles, comme « Header » ou « Page 404 », sont éditables et affichées.

## SEO

Yoast SEO est le propriétaire principal des titres, descriptions, canonicals,
balises sociales, sitemaps et données structurées du site.

Le thème conserve uniquement les adaptations liées à son modèle de données :

- `inc/front/breadcrumb.php` fournit le parcours visuel à Yoast et empêche un
  second `BreadcrumbList` autonome lorsque le plugin est actif ;
- `inc/config/seo.php` déclare les entrées du CPT `news` comme
  `NewsArticle` ;
- le titre, la date et l’image du groupe ACF `hero-news` alimentent le
  `NewsArticle` ;
- l’image du hero sert de secours Open Graph et Twitter uniquement lorsqu’une
  image sociale propre à la News n’a pas été définie dans Yoast.

Le CPT `news` doit conserver le support WordPress `author`, requis par Yoast
pour produire un schéma Article.

Les balises `<link rel="next">` et `<link rel="prev">` de Yoast sont
désactivées. Les archives conservent leur pagination HTML crawlable, sans
provoquer de préchargement automatique de la page adjacente par le navigateur.

## Configuration à faire pour chaque site

Dans les réglages Yoast :

1. renseigner la représentation du site (`Organization` ou `Person`) et son
   logo ;
2. définir les modèles de titres et descriptions adaptés au projet ;
3. configurer l’image sociale par défaut ;
4. désactiver l’indexation et les sitemaps des types de contenus, taxonomies ou
   archives d’auteurs réellement inutilisés.

Les descriptions et images saisies manuellement dans Yoast restent prioritaires
sur les secours du thème. Ne pas ajouter un second générateur global de
`WebSite`, `Organization`, `Article` ou `BreadcrumbList`.

Après une modification SEO, régénérer les pages avec WP Static afin que les
balises de la version statique soient mises à jour.
