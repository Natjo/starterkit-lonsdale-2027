# Configuration SEO

## Content Security Policy

La CSP front est désactivée sans `wp-content/theme-csp.json`. Forge écrit ce
fichier uniquement après « Appliquer au site » : `version: 1`, `enabled` booléen,
`mode: report|enforce`, `policy` (une ligne, 8 Ko maximum). Aucun secret n'y figure.
L'administration, REST, AJAX et WP-CLI ne reçoivent pas cette politique.

`inc/config/csp.php` prépare l'en-tête avec `wp_headers`, puis le tampon de rendu
existant finalise la politique après les modifications de `ThemeAssets`.
Les hashes SHA-256 portent sur les octets exacts (fins de ligne HTML normalisées)
des CSS critical/once, y compris la concaténation tardive des heroes, et des scripts
WordPress `app-js-js-extra/before/after`. Ils sont ajoutés aux directives
`style-src-elem` / `script-src-elem` sans assouplir les attributs style/onclick.
Une configuration mal formée passe en blocage restrictif et écrit une erreur PHP.
Une politique concurrente PHP n'est pas écrasée : l'erreur est journalisée.

Un producteur PHP fiable peut appeler `Theme_CSP::registerInline('script', $code)`
ou `registerInline('style', $css)` sur le contenu exact qu'il imprime.
`theme_csp_inline_script_ids` permet une liste explicite de scripts WordPress
additionnels. Ne jamais appeler ces APIs sur du HTML éditorial, ni autoriser un
script simplement parce que son ID apparaît dans la page. Les plugins non
intégrés restent bloqués/signalés. Les attributs inline, scripts injectés après
chargement et fragments AJAX contenant de nouveaux styles inline ne sont pas
automatiquement autorisés. Une CSP stricte peut nécessiter de les externaliser.

Commencer en Report-Only et tester toutes les pages avant le blocage. Retirer les
anciennes balises meta et vérifier Apache/CDN : PHP ne peut pas détecter un en-tête
ajouté après sa réponse. Ne pas flusher/modifier le HTML après la finalisation.
La réponse est tamponnée, ce qui conserve tout le HTML en mémoire. Au-delà de
16 Ko d'en-tête, les hashes ne sont pas ajoutés et une erreur est journalisée.

WP Static conserve politique et HTML dans une enveloppe atomique et les restitue
dans son service PHP (pré-WordPress compris). Les anciens caches sont contournés
après une modification du JSON et doivent être régénérés. Un serveur qui sert
directement les fichiers HTML, un export ZIP statique ou un autre plugin de cache
doit intégrer lui-même les en-têtes : ce mode n'est pas automatiquement couvert.

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
