# Front — composants et templates

Référence des helpers PHP utilisés pour rendre les composants du thème avec
WordPress 6+.

---

## Contexte

Les templates de composants vivent sous :

```
template-parts/components/{nom}/{nom}.php
```

Exemples : `icon/icon.php`, `text/text.php`, `btn/btn.php`, etc.

Ils consomment un payload associatif via la variable **`$params`**.

---

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
dossier `select-lang`. Il est toutefois préférable d’utiliser directement le
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
        get_template_part("template-parts/components/{$folder}/{$folder}");
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
| `hero()` | `get_template_part("heros/hero-{$name}/hero-{$name}", null, $args)` | `set_template_args($args)` + `get_template_part("template-parts/heros/hero-{$name}/hero-{$name}")` |
| `card()` | `get_template_part("cards/{$name}/{$name}", null, $args)` | validation du template + isolation de `template_args` + `get_template_part("template-parts/cards/{$name}/{$name}")` |
| `strate()` | `get_template_part("template-parts/strates/strate-{$name}/strate-{$name}", null, $args)` | validation + isolation de `template_args` + template dans `template-parts/strates/` |

### Code cible

```php
function common(string $name): void
{
    $name = trim($name);
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;

    theme_queue_module_assets("common/{$name}/{$name}");
    get_template_part("template-parts/common/{$name}/{$name}");
}

function hero(string $name, array $args = []): void
{
    set_template_args($args);
    get_template_part("template-parts/heros/hero-{$name}/hero-{$name}");
}

function card(mixed $name, mixed $data = []): void
{
    $name = resolve_card_name($name);
    $args = resolve_card_data($data);
    if ($name === '' || $args === null) return;

    $previousArgs = get_query_var('template_args', null);
    set_template_args($args);

    try {
        get_template_part("template-parts/cards/{$name}/{$name}");
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
        get_template_part("template-parts/strates/strate-{$name}/strate-{$name}");
    } finally {
        set_query_var('template_args', $previousArgs);
    }
}
```

`card()` accepte soit un ID de post positif, soit un tableau associatif non
vide. Un ID charge les champs ACF et ajoute `post_id`. Un tableau contenant
`post_id` charge également le post, puis ses autres valeurs surchargent les
champs récupérés.

### CSS propre aux templates WordPress

Un import marqué `split` est compilé séparément et chargé uniquement pour le
template WordPress portant le même nom :

```scss
@import "pages/search" split;
```

`search.php` enregistre alors automatiquement `pages/search.css`. La même
convention s'applique à `404.php`, `archive.php`, `single-news.php` ou
`front-page.php`. Aucun appel à `theme_queue_module_assets()` n'est nécessaire
dans ces templates. Si aucun fichier CSS compilé ne correspond au template,
aucune balise `<link>` n'est produite.

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

- [ ] Copier / déplacer les templates vers `template-parts/components/{nom}/{nom}.php`
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
| `template-parts/components/*/` | Markup de chaque composant |
| `template-parts/common/*/` | Header, footer, etc. |
| `template-parts/heros/*/` | Heros de page |
| `template-parts/cards/*/` | Cards (news, people, etc.) |
| `template-parts/common/footer/footer.php` | Exemple d’usage des composants procéduraux |

---

*Dernière mise à jour : juin 2026 — WordPress 6.1+ / PHP 8.3*
