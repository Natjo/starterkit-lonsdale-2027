# Front — composants et templates

Référence des helpers PHP utilisés pour rendre les composants du thème avec
WordPress 6+.

---

## Contexte

Les templates de composants vivent sous :

```
template-parts/components/{nom}.php
```

Exemples : `icon.php`, `text.php`, `btn.php`, etc.

Ils consomment un payload associatif via la variable **`$params`**.

---

## Normalisation des chaînes

`theme_string(mixed $value, string $default = '')` convertit les scalaires
et les objets `Stringable` en chaînes, puis retire les espaces aux extrémités.
Les autres valeurs retournent `$default`. Ce helper est partagé par les
composants, cards, strates, éléments common et helpers front : le réutiliser
plutôt que déclarer une fonction anonyme `$to_string` dans chaque fichier.
Il ne remplace pas l’échappement à la sortie.

## Normalisation des liens

`theme_link(mixed $link, mixed $attributes = [])` normalise un lien associatif
pour les composants, cards et templates. Il retourne toujours `url`, `title`,
`target`, `rel`, `disabled` et `attributes`. Les entrées invalides ou
positionnelles sont traitées comme un lien vide.

L’URL est nettoyée avec `esc_url_raw()` ; appliquer `esc_url()` à sa sortie
HTML. Le titre et les attributs demandent également leur échappement de sortie.
`target` accepte `_self`, `_blank`, `_parent` et `_top`. Pour `_blank`, `rel`
contient automatiquement `noopener noreferrer`. Le second argument permet de
normaliser `rel`, `aria-disabled` et les attributs libres du lien.

```php
$link = theme_link($args['link'] ?? []);
$url = $link['url'];
$target = $link['target'];
```

## 1. API des composants

Le projet conserve uniquement ces trois fonctions :

```php
component('nom', [...]);
component_classes(...);
component_attributes(...);
```

- `component()` valide le nom, charge les assets puis le template.
- `component_classes()` normalise, nettoie et échappe les classes CSS.
- `component_attributes()` filtre et échappe les attributs HTML.

Les noms avec un underscore sont convertis en tirets : `select_lang` cible le
fichier `select-lang.php`. Il est toutefois préférable d’utiliser directement le
nom canonique avec des tirets.

### Code cible

```php
function component(string $name, array $args = []): void
{
    $folder = str_replace('_', '-', trim($name));
    if ($folder === '' || !preg_match('/^[A-Za-z0-9-]+$/', $folder)) return;

    theme_queue_module_assets("components/{$folder}/{$folder}");
    $previousParams = get_query_var('params', null);
    set_query_var('params', $args);

    try {
        get_template_part("template-parts/components/{$folder}");
    } finally {
        set_query_var('params', $previousParams);
    }
}
```

Le second argument est toujours un tableau associatif transmis tel quel dans
`$params`. Chaque composant décrit précisément son payload dans son README.

---

## 2. `methods.php` — `common()`, `hero()`, `card()`, `strate()`

`common()` est volontairement différent des renderers éditoriaux. Il reçoit
uniquement le nom de la zone globale :

```php
common('header-nav');
common('footer');
```

Le template common récupère directement ses données globales dans WordPress :
emplacements de menus, options du site ou options ACF. Il ne reçoit aucun
payload et n'appelle pas `get_template_args()`.

`hero()`, `card()` et `strate()` transmettent en revanche leurs données avec
`set_template_args($args)`. Leurs templates les récupèrent via
`get_template_args()` (query var `template_args`).

> **Piège WordPress :** ne pas utiliser `set_query_var('args', …)`. La fonction `load_template()` déclare déjà `$args = []` ; avec `extract(..., EXTR_SKIP)`, la query var `args` est ignorée et le template reçoit toujours un tableau vide.

### Changements

| Helper | Avant | Après |
|--------|-------|-------|
| `common()` | Template global avec arguments | nom uniquement + données récupérées depuis WordPress |
| `hero()` | Appel direct avec arguments WordPress | `set_template_args($args)` + `get_template_part("template-parts/heros/hero-{$name}")` |
| `card()` | Template imbriqué dans un dossier homonyme | validation du template + isolation de `template_args` + `get_template_part("template-parts/cards/{$name}")` |
| `strate()` | Template imbriqué dans un dossier homonyme | validation + isolation de `template_args` + template plat dans `template-parts/strates/` |

### Code cible

```php
function common(string $name): void
{
    $name = trim($name);
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;

    theme_queue_module_assets("common/{$name}/{$name}");
    get_template_part("template-parts/common/{$name}");
}

function hero(string $name, array $args = []): void
{
    set_template_args($args);
    get_template_part("template-parts/heros/hero-{$name}");
}

function card(mixed $name, mixed $data = []): void
{
    $name = resolve_card_name($name);
    $args = resolve_card_data($data);
    if ($name === '' || $args === null) return;

    $previousArgs = get_query_var('template_args', null);
    set_template_args($args);

    try {
        get_template_part("template-parts/cards/{$name}");
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}

function strate(string $name, array $args = []): void
{
    $name = trim($name);
    if ($name === '' || !preg_match('#^[A-Za-z0-9_-]+$#', $name)) return;

    $previousArgs = get_query_var('template_args', null);
    set_template_args($args);

    try {
        get_template_part("template-parts/strates/strate-{$name}");
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}
```

`card()` accepte soit un ID de post positif, soit un tableau associatif non
vide. Un ID charge les champs ACF, les données normalisées de
`theme_post_card_item()` et ajoute `post_id`. Les posts de statut non public ou protégés
par mot de passe sont exclus, indépendamment des droits du visiteur. Un tableau contenant
`post_id` charge également le post, puis ses autres valeurs surchargent les
champs récupérés.

### Preload des fontes locales

`fontPreload()` génère les preloads WOFF2 à partir des noms de fichiers sans
extension :

```php
fontPreload(['SequelSansLight', 'SequelSansBook', 'SequelSansHeavy']);
```

La fonction accepte uniquement une liste de noms composés de lettres, chiffres,
underscores et tirets. Elle ignore les entrées invalides, les fichiers absents
et les doublons, y compris entre plusieurs appels. Les fichiers doivent être
présents dans `assets/fonts/`; les balises générées incluent automatiquement
`as="font"`, `type="font/woff2"` et `crossorigin`.

### Cache ACF et préchargement des contenus

`theme_get_fields()` conserve les champs ACF formatés ou bruts d'une cible
pendant toute la requête PHP. Il accepte un ID de post, une cible ACF comme
`option`, ou utilise le contenu interrogé lorsque la cible est omise :

```php
$page_fields = theme_get_fields();
$news_fields = theme_get_fields($news_id);
$raw_options = theme_get_fields('option', false);
```

`theme_get_strate_anchor_items()` transforme les `options.name` d’une liste de
strates en payload pour `anchor-nav`. Les IDs produits correspondent à ceux que
`options()` ajoute aux sections :

```php
$fields = theme_get_fields();
$anchor_items = theme_get_strate_anchor_items($fields['strates'] ?? []);

component('anchor-nav', ['items' => $anchor_items]);
```

`theme_prime_posts()` extrait les IDs directs et les clés `post_id` d'une
collection, puis charge en lots les objets WordPress et leurs métadonnées. Les
IDs déjà traités pendant la requête sont ignorés :

```php
theme_prime_posts([123, ['post_id' => 456]]);
```

`theme_post_card_item($post_id)` construit le payload commun des cards :
`post_id`, type, date, titre, extrait, image mise en avant et lien. Le filtre
`theme_post_card_item` permet à un CPT d'adapter ce payload pour tous les
listings, y compris les strates `pushes` et `Theme_Listing`.

Le starter fournit des groupes spécifiques par Content Type, notamment
**Card - news**, **Card - team**, **Card - project** et **Card - expertises**,
avec leurs données regroupées sous `card`.
Les clés canoniques sont lues directement ; Team mappe `card.function` vers `text`.
Le groupe générique **Field - Card**, non affecté et sans données, a été retiré.
Le titre, l’extrait et l’image WordPress restent les valeurs de repli ; le
permalien reste celui du post et son libellé suit le titre retenu.

Chaque groupe spécifique fournit son propre champ `card`, affecté à son CPT.
Les valeurs canoniques de ce groupe ont priorité sur le mapping métier.

Un CPT peut déclarer les noms de ses champs métier avec le filtre
`theme_card_field_map` :

```php
add_filter('theme_card_field_map', static function (array $map, string $post_type): array {
    return $post_type === 'event'
        ? ['date' => 'event_date', 'text' => 'event_summary', 'image' => 'event_image']
        : $map;
}, 10, 2);
```

Les clés possibles sont `title`, `text`, `date`, `image` et `images`.
`images` désigne un groupe desktop/mobile d’IDs. Le payload conserve les deux
variantes et fournit aussi `image` (desktop, sinon mobile) aux cards simples. Les valeurs sont des
chemins de champs ACF (racine ou imbriqués avec un point) ; aucune convention
n’est imposée aux champs métier des CPT. Les dates
ACF `Y-m-d` et `Ymd` sont acceptées. Un mapping absent, un champ vide ou une
date invalide laisse jouer le repli suivant. Il n’est pas nécessaire de saisir
la même date une deuxième fois dans `card.date`.

`theme_prime_attachments()` est réservé aux payloads explicitement dédiés aux
images. Il reconnaît les IDs directs, les tableaux ACF avec `ID` ou `id`, ainsi
que les clés `image`, `images`, `desktop` et `mobile`. Il ne parcourt pas les
autres nombres d'un payload, afin de ne pas confondre une dimension ou une
valeur éditoriale avec un média :

```php
theme_prime_attachments([
    ['image' => 123],
    ['images' => ['desktop' => 456, 'mobile' => 789]],
]);
```

Les composants de collection `list`, `slider` et `marquee` utilisent ces
helpers automatiquement dans leurs modes card ou picture. Les strates doivent
les appeler avant toute validation individuelle avec `get_post_type()`.
`theme_attachment_url_to_post_id()` déduplique aussi les résolutions d'une même
URL de médiathèque pendant la requête. Ces caches sont locaux à la requête et
ne nécessitent ni Redis ni cache de page.

### Contexte des pages filtrées

`Theme_Listing` fournit une façade de rendu autour de
`theme_build_filters_context()`. Elle centralise la lecture et la validation
des paramètres GET, la découverte facultative des taxonomies, le tri et la
requête WordPress. Le template conserve sa configuration, l’emplacement de
chaque formulaire et l’assemblage des cards :

```php
$filters = new Theme_Listing([
    'post_type' => 'project',
    'search' => [
        'label' => 'Rechercher',
        'placeholder' => 'Rechercher un projet',
        'acf_fields' => ['hero-page_title'],
    ],
    'taxonomies' => true,
    'taxonomy_relation' => 'AND',
    'ajax' => true,
    'ajax_group' => 'project-filters',
    'ajax_target' => 'project-results',
    'sort' => [
        'default' => 'date_desc',
        'options' => [
            'date_desc' => ['label' => 'Plus récents', 'orderby' => 'date', 'order' => 'DESC'],
            'title_asc' => ['label' => 'Titre A–Z', 'orderby' => 'title', 'order' => 'ASC'],
        ],
    ],
]);
```

Sans `acf_fields`, la recherche utilise le moteur natif de WordPress. Avec
`search.acf_fields`, le champ de recherche unique cible les métadonnées ACF
indiquées avec une comparaison `LIKE`. `acf_filters` sert aux contrôles
indépendants rendus dans `filters()`.

Les formulaires peuvent ensuite être placés indépendamment :

```php
<div class="filters-search-bar">
    <?php $filters->search(); ?>
</div>

<aside class="main-sidebar">
    <?php $filters->filters(); ?>
</aside>

<div class="filters-toolbar">
    <?php $filters->sort(); ?>
</div>

<div id="<?= esc_attr($filters->target()) ?>" aria-live="polite">
    <?php $filters->results_message(); ?>
    <?php component('list', [
        'items' => $filters->items(),
        'card' => $filters->card(),
    ]); ?>
</div>
```

`results_message()` ne rend rien avant une recherche. Elle affiche ensuite
« Aucun résultat », « **1** résultat » ou « **X** résultats » et accepte une
option `classes` comme les méthodes de formulaires. Ses textes sont
personnalisables pour WPML avec `messages.zero`, `messages.singular` et
`messages.plural`. Le placeholder `{count}` rend le nombre en `<strong>`.

`search()`, `filters()` et `sort()` acceptent aussi une option de rendu
`classes`, sous forme de chaîne ou de tableau. Elle complète les classes du
groupe sans les remplacer :

```php
<?php $filters->search(['classes' => 'filters-search-wide']); ?>
<?php $filters->filters(['classes' => ['filters-sidebar', 'theme-dark']]); ?>
<?php $filters->sort(['classes' => 'filters-sort-compact']); ?>
```

`facets()` reste disponible comme alias rétrocompatible de `filters()`.

La classe expose `archive_title()`, `target()`, `items()`, `card()`,
`has_results()`, `current_page()`, `total_pages()`, `post_type_label()` et
`context()`. `form($name)` permet de rendre un groupe personnalisé. La classe
ne rend volontairement ni les cards ni la pagination : leur HTML reste sous le
contrôle du template.

`taxonomies` accepte `true` pour toutes les taxonomies publiques du CPT, une
liste de slugs pour limiter les filtres, ou `false`. Les options complémentaires
sont `acf_filters`, `meta_relation`, `hide_empty_terms`, `posts_per_page`,
`card`, `submit_label`, `reset_label`, `aria_label`, `result_labels`, `ajax`,
`ajax_group`, `ajax_target` et `groups`.
Chaque filtre ACF déclare au minimum son `name`, sa clé meta `field` et son
`control`. Les contrôles texte utilisent `LIKE` par défaut ; les autres valeurs
simples utilisent `=`. `compare`, `options`, `placeholder`, `input_value`,
`stored_value`, `value_relation`, `min` et `max` permettent d’ajuster le
comportement. Le filtre
`theme_filters_result_item` permet d’enrichir chaque payload de card.

Sans configuration `groups`, `Theme_Listing` crée les groupes `search`,
`facets` et `sort`. Le helper procédural conserve quant à lui son comportement
historique dans `form`. Avec
`groups`, il expose en plus un payload par emplacement dans `forms`. Les groupes
peuvent assembler `search`, `taxonomies`, `acf` et `sort`. Chaque formulaire
reçoit sous forme de champs cachés les valeurs actives des groupes qu’il
n’affiche pas : recherche, filtres et tri restent donc indépendants même sans
JavaScript. Les options d’un groupe sont `controls`, `submit_label`,
`aria_label`, `classes`, `show_reset`, `show_result_count` et
`ajax_submit_on_change`.

Lorsque `ajax` vaut `true`, les formulaires de `forms` partagent le groupe et la
cible configurés. La page doit attribuer l’identifiant retourné dans
`ajax_target` au conteneur de la liste et de sa pagination.

### CSS propre aux templates WordPress

Un import marqué `once` est compilé séparément puis injecté inline une seule
fois, uniquement pour le template WordPress portant le même nom :

```scss
@import "pages/search/search" once;
```

Le build produit alors `assets/css/once/pages/search/search.css`. `search.php`
l'enregistre automatiquement et son contenu est rendu dans
`<style id="once-pages-search">`. La même convention s'applique à `404.php`,
`archive.php`, `single-news.php` ou `front-page.php`. Aucun appel à
`theme_queue_module_assets()` n'est nécessaire dans ces templates. Si aucun
fichier CSS compilé ne correspond au template, aucune balise `<style>` n'est
produite.

Le CSS critique nécessaire au premier écran reste injecté dans
`<style id="critical-css">`. `app.css` et chaque fichier marqué `split` restent
des feuilles externes classiques, mises en cache entre les pages. Le CSS
`split` d'un composant, common, card ou d'une strate est émis une seule fois
juste avant le premier rendu de ce module dans le `<body>`.

Le suffixe `once` peut aussi être appliqué à un composant, une card, un common
ou une strate de petite taille et rarement réutilisé. Son CSS est alors injecté
dans une balise `<style>` juste avant le premier rendu du module et dédupliqué
pour toute la réponse. `once` décrit le mode de chargement, pas la page sur
laquelle le module est autorisé : ce sont toujours les templates et les données
ACF qui déterminent où il apparaît.

### Usage côté templates

```php
<?php
hero("homepage", [
    "suptitle" => "Internal <strong>Ai hub</strong>",
    "title" => "Ai at<br>the service<br>of creativity",
    "text" => "<p>…</p>",
    "items" => [
        ["images" => THEME_ASSETS . "img/tmp/hero-1.jpg"],
    ],
]);
?>
```

Dans le template (`hero-homepage.php`, `header-nav.php`, etc.) :

```php
$args = get_template_args();
```

---

## 3. Checklist migration — autre environnement

### Fichiers PHP à mettre à jour

- [ ] `inc/front/components.php` — `component_classes()` et `component_attributes()`
- [ ] `inc/front/methods.php` — `component()`, `common()`, `hero()`, `card()`, `strate()`

### Structure de fichiers

- [ ] Copier / déplacer les templates vers `template-parts/components/{nom}.php`
- [ ] Vérifier que chaque composant appelé en PHP existe à cet emplacement

### Dépendances

- [ ] `functions.php` charge bien `inc/front/components.php` puis `inc/front/methods.php`
- [ ] Helpers utilisés par les composants disponibles : `normalize_args()`, `html_attributes()`, `sanitize_class_list()`, constantes `THEME_ASSETS`, etc.

### Assets liés aux composants

- [ ] `assets/img/icons.svg` présent (utilisé par `component('icon', [...])` via `<use href="…/icons.svg#id">`)
- [ ] Sprite SVG : si les icônes ne s’affichent pas visuellement malgré le HTML correct, envisager l’inclusion inline du sprite dans le layout

### Tests rapides

```php
<?php component('icon', ['name' => 'star-stroke', 'width' => 38, 'height' => 38]); ?>
<?php component('text', ['text' => 'Test <br> composant']); ?>
```

Dans le HTML généré, attendu :

- un `<svg class="icon icon-star-stroke" …>` avec `<use href="…/icons.svg#star-stroke">`
- un `<div class="text">` avec le contenu

---

## 4. Récapitulatif des causes de non-affichage (debug)

| Symptôme | Cause probable |
|----------|----------------|
| Rien ne s’affiche, pas d’erreur PHP | `$params` ou `$args` vide (ancien 3ᵉ arg `get_template_part`, ou query var `args` ignorée par `load_template`) |
| Rien ne s’affiche | Template introuvable (`common/`, `heros/`, `cards/` au lieu de `template-parts/…`) |
| Texte OK, icône invisible | Sprite SVG externe non chargé ou CSS (`fill`, `opacity`, `z-index`) |
| `component('icon', [...])` avec nom invalide | `sanitize_html_class()` vide le `name` → return anticipé dans `icon.php` |

---

## 5. Références dans ce projet

| Fichier | Rôle |
|---------|------|
| `inc/front/components.php` | Helpers de classes et d’attributs |
| `inc/front/methods.php` | Dispatcher `component()` et helpers de templates |
| `inc/front/contact-form.php` | Traitement, validation et état du formulaire de contact |
| `template-parts/components/*.php` | Markup de chaque composant |
| `template-parts/common/*.php` | Header, footer, etc. |
| `template-parts/heros/*.php` | Heros de page |
| `template-parts/cards/*.php` | Cards (news, people, etc.) |
| `assets/{famille}/{module}/README.md` | Contrat local et assets du module |
| `template-parts/common/footer.php` | Exemple d’usage des composants procéduraux |

### Formulaire de contact

`Theme_Contact_Form` centralise la lecture de la requête, le nonce, la
validation, l’envoi avec `wp_mail()` et la redirection PRG après succès. Le
template conserve uniquement le rendu :

```php
$contact_form = new Theme_Contact_Form([
    'action_url' => get_permalink(),
    'ajax' => true,
]);
$contact_form->handle();
```

Les méthodes `value()`, `error()`, `is_sent()`, `action_url()` et
`nonce_field()` exposent l’état nécessaire au formulaire. Le destinataire et
les messages `expired`, `invalid` et `failed` peuvent être fournis au
constructeur, notamment pour un site multilingue.

L’option `ajax` est désactivée par défaut. Lorsqu’elle vaut `true`, le formulaire
doit utiliser le module `pages/template-contact` et transmettre l’URL retournée
par `endpoint_url()` dans `data-contact-endpoint`. Le module envoie les données
à `starter/v1/contact`; en l’absence de JavaScript ou en cas d’erreur réseau,
le formulaire POST classique reste disponible.

---

*Dernière mise à jour : juin 2026 — WordPress 6.1+ / PHP 8.3*

### Région AJAX sans formulaire

`$listing->results_attributes()` génère l’ID de la région de résultats et,
si `ajax` est actif, ses attributs d’hydratation `components/filters`. Placer
ces attributs sur le conteneur stable de la liste et de sa pagination permet
la navigation AJAX même sans recherche, facette ni tri.

### Recherche simple des listings

`Theme_Listing::search()` rend le composant `search` pour le groupe standard,
avec le champ et le bouton seuls. Les paramètres des facettes et du tri restent
dans des champs cachés pour les soumissions GET et dans le groupe AJAX partagé.
Les groupes personnalisés combinant plusieurs contrôles conservent le rendu
`filters`. Les manifestes des pages qui affichent la recherche déclarent donc
les composants `search` et `filters`.
