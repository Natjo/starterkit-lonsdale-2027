# Composants du thème

Ce dossier contient les assets et la documentation des composants PHP
réutilisables du thème. Pour utiliser ou modifier un composant, lire d’abord le
`README.md` placé dans son dossier d’assets.

## API unique

Tous les composants sont appelés avec :

```php
component('nom', [
    'cle' => 'valeur',
]);
```

Exemples :

```php
component('title', [
    'title' => 'Titre de section',
    'classes' => 'title-2',
]);

component('picture', [
    'image' => $args['image'] ?? null,
    'size' => 'large',
]);
```

Le second argument est toujours un tableau associatif. Les tableaux
positionnels ne font pas partie du contrat des composants.

`component()` :

1. valide le nom du composant ;
2. convertit les underscores en tirets (`select_lang` devient `select-lang`) ;
3. enregistre ses assets avec `theme_queue_module_assets()` ;
4. transmet le payload dans `$params` ;
5. charge `template-parts/components/<nom>.php`.

Utiliser de préférence le nom canonique avec des tirets :

```php
component('select-lang', [...]);
```

Un nom absent ou invalide est signalé avec `_doing_it_wrong()` lorsque
`WP_DEBUG` est actif.

## Helpers disponibles

### Classes CSS

```php
$classes = component_classes('badge', $params['classes'] ?? '');
```

`component_classes()` accepte des chaînes et des tableaux imbriqués. Il retire
les valeurs invalides, nettoie la liste, déduplique les classes et retourne une
chaîne déjà échappée.

### Attributs HTML

```php
$attributes = component_attributes(
    $params['attributes'] ?? [],
    ['class', 'id']
);
```

`component_attributes()` :

- accepte uniquement un tableau d’attributs ;
- filtre les noms dangereux, les attributs `on*` et `srcdoc` ;
- ignore les tableaux, objets et autres valeurs complexes ;
- échappe les noms et les valeurs ;
- ignore les attributs réservés par le composant ;
- ajoute automatiquement l’espace initial lorsque le résultat n’est pas vide.

Utilisation :

```php
<div class="<?= $classes ?>"<?= $attributes ?>></div>
```

## Contrat d’un composant

Chaque composant est autonome. Son template normalise son payload, valide ses
données, sécurise son rendu et décide si un payload vide doit produire du HTML.
Le comportement exact est documenté dans son propre README.

Squelette recommandé :

```php
<?php
/** @var array<int|string, mixed>|null $params */

$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;

$args = normalize_args($params, [
    'title' => '',
    'classes' => '',
    'attributes' => [],
]);

$title = is_scalar($args['title']) || $args['title'] instanceof Stringable
    ? trim((string) $args['title'])
    : '';

if ($title === '') return;

$classes = component_classes('example', $args['classes']);
$attributes = component_attributes($args['attributes'], ['class']);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?= esc_html($title) ?>
</div>
```

Règles communes :

- utiliser `esc_html()` pour un libellé simple ;
- utiliser `wp_kses_post()` uniquement pour un contenu éditorial autorisant du
  HTML filtré ;
- utiliser `esc_url()` pour une URL ;
- réserver dans `component_attributes()` tous les attributs générés par le
  template (`class`, `id`, `href`, `type`, attributs ARIA, etc.) ;
- normaliser explicitement les booléens avec `filter_var()` ;
- ne pas ajouter d’appel de traduction dans un composant : les libellés
  variables sont fournis par l’appelant ;
- ne pas récupérer implicitement des données ACF dans un composant générique ;
- documenter toutes les clés publiques dans le README local.

## Fichiers et assets

Un composant possède un template PHP plat et peut avoir des fichiers co-localisés
dans son dossier d’assets :

```text
template-parts/components/<nom>.php
assets/components/<nom>/README.md
assets/components/<nom>/<nom>.scss
assets/components/<nom>/<nom>.js
```

Le PHP contient le contrat et le markup. Le SCSS contient uniquement les styles
du composant. Le JavaScript est facultatif et doit exporter une fonction
d’initialisation par défaut.

Exemple d’hydratation :

```php
<div
    class="example"
    data-module="components/example"
    data-context="@visible true">
</div>
```

```js
export default function initExample(root) {
    const controller = new AbortController();
    const { signal } = controller;
    const onClick = () => {};

    root.addEventListener('click', onClick, { signal });

    return () => controller.abort();
}
```

Le retour de la fonction est le nettoyage du module. Pour un module qui doit
être suspendu hors écran, elle peut retourner un objet avec `destroy`, `pause`
et `resume`. Le chargeur d’hydratation s’occupe du chargement différé et appelle
le nettoyage lorsque le nœud est retiré du DOM.

## Choisir le bon renderer

| Besoin | Appel | Emplacement du template | Variable reçue |
| --- | --- | --- | --- |
| Composant générique | `component('title', [...])` | `template-parts/components/` | `$params` |
| Card | `card('news', 123)` ou `card('news', [...])` | `template-parts/cards/` | `get_template_args()` |
| Strate | `strate('slider', [...])` | `template-parts/strates/` | `get_template_args()` |
| Élément commun | `common('footer')` | `template-parts/common/` | Données globales WordPress |
| Hero | `hero('homepage', [...])` | `template-parts/heros/` | `get_template_args()` |

Ne pas utiliser `component()` pour rendre une card, une strate, un common ou
un hero.

## Composants disponibles

```text
accordion       anchor-nav      autocomplete    badge           btn
breadcrumb      date            dialog          eyebrow
field           filters
header          icon            image           intro
link            list            lottie          marquee         newsletter
pagination      picto           picture         quote           search
section-snap    select
select-custom   select-lang     shares          slider
socials
tab             table           tag             text
title           tooltip         video
```

## Documentation locale

Le `README.md` situé dans `assets/components/<nom>/` est la source de vérité pour :

- les clés du payload ;
- leurs types et valeurs par défaut ;
- les exemples d’appel ;
- les règles d’échappement ;
- les comportements particuliers et les limites.

Une IA doit lire ce README général, puis le README du composant concerné, avant
d’ouvrir son PHP pour modifier son comportement.

## Validation

Les contrats sont conservés pendant le développement du starter :

```bash
docker compose -f .docker/docker-compose.yml exec -T wordpress \
  php /var/www/html/wp-content/themes/starterkit-lonsdale-2027/tests/components-contract.php
```

Ils vérifient notamment les payloads, l’échappement, les attributs, les cards,
les composants interactifs et la restauration des contextes de templates. Les
fichiers `*-browser-contract.html` couvrent les comportements nécessitant un
vrai navigateur.
