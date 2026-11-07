# Heroes du thème

Les heroes sont les en-têtes éditoriaux placés au début d’une page. Ils
assemblent des composants, portent le `h1` et peuvent afficher le média
principal de la page.

Pour créer ou modifier un hero, lire ce README puis le `README.md` situé dans
son dossier. Le README local est la source de vérité de son payload.

## API

```php
hero('homepage');
```

Le premier argument correspond au suffixe du fichier :

```text
hero('homepage')
→ template-parts/heros/hero-homepage.php
→ assets/heros/hero-homepage/hero-homepage.scss
→ assets/heros/hero-homepage/hero-homepage.js
```

Ne pas inclure le préfixe `hero-` dans l’appel. Le nom accepte uniquement les
lettres, chiffres, tirets et underscores. Un nom invalide ou un template absent
ne produit aucun rendu.

## Données ACF ou payload explicite

Sans second argument, `hero()` charge automatiquement le champ ACF
`hero-{nom}` du contenu courant :

```php
hero('homepage'); // get_field('hero-homepage')
```

Sur un contenu singulier dont le groupe n’a jamais été enregistré, le helper
résout le champ par sa clé ACF parmi les groupes applicables pour récupérer
les valeurs par défaut visibles dans le BO. Ce repli ne remplace pas un groupe
déjà enregistré, même si ses champs sont vides.

Un tableau associatif fourni explicitement remplace entièrement cette lecture :

```php
hero('homepage', [
    'suptitle' => 'Surtitre',
    'title' => 'Titre de la page',
    'text' => '<p>Introduction éditoriale.</p>',
    'images' => [
        'desktop' => 123,
        'mobile' => 456,
    ],
]);
```

Même un tableau vide désactive la récupération ACF :

```php
hero('homepage', []);
```

Le template récupère toujours les données avec :

```php
$args = get_template_args();
```

Le contexte précédent est restauré après le rendu. Un hero peut donc appeler
des composants sans écraser les arguments de son parent.

## Structure recommandée

```php
<?php
$args = get_template_args();
?>

<header class="hero hero-example">
    <div class="hero-content">
        <?php component('eyebrow', [
            'eyebrow' => $args['suptitle'] ?? '',
        ]); ?>

        <?php component('title', [
            'title' => $args['title'] ?? '',
            'hx' => 1,
            'classes' => 'title-1',
        ]); ?>

        <?php component('text', [
            'text' => $args['text'] ?? '',
            'classes' => 'intro',
        ]); ?>
    </div>
</header>
```

Règles :

- rendre le hero avec `hero()` et jamais avec `component()` ou un
  `get_template_part()` direct ;
- utiliser les composants existants au lieu de recopier leur logique ;
- transmettre des payloads explicites aux composants ;
- laisser chaque composant normaliser ses données et décider s’il doit rendre
  du HTML ;
- conserver un seul `h1` principal par page ;
- échapper dans le template uniquement les valeurs qu’il rend directement ;
- documenter toutes les clés publiques dans le README local ;
- ne pas utiliser `options()`, réservé aux strates éditoriales.

## Image principale et LCP

Une image visible immédiatement, occupant une part importante du hero, est
généralement candidate au Largest Contentful Paint (LCP). Elle ne doit pas être
chargée en lazy-loading.

Utiliser le composant `picture` avec `preload => true` :

```php
component('picture', [
    'images' => $args['images'] ?? null,
    'desktop_size' => 'large',
    'mobile_size' => 'medium_large',
    'classes' => 'hero-media',
    'alt' => '',
    'preload' => true,
]);
```

Cette option :

- injecte un `<link rel="preload" as="image" fetchpriority="high">` dans le
  `<head>` ;
- retire `loading="lazy"` ;
- ajoute `fetchpriority="high"` ;
- précharge le WebP lorsqu’il est réellement utilisé ;
- ajoute deux preloads conditionnels si des variantes desktop et mobile sont
  fournies.

`critical => true` retire également le lazy-loading et ajoute
`fetchpriority="high"`, mais ne crée pas de `<link rel="preload">`. Pour une
image LCP connue dès le rendu du hero, préférer `preload => true`.

Ne précharger qu’une image principale par page. Une image secondaire, masquée
au chargement ou située sous la ligne de flottaison doit conserver le
comportement normal du composant.

Pour une image décorative, fournir explicitement :

```php
'alt' => '',
```

Le preload doit cibler exactement la ressource choisie par le `<picture>`.
Éviter de coder manuellement une autre URL dans `header.php`.

## CSS critique

Déclarer le SCSS du hero dans `assets/app.scss` avec le suffixe `critical` :

```scss
@import "heros/hero-example/hero-example" critical;
```

Le build produit le CSS dédié du hero. Lors de l’appel à `hero('example')`, le
renderer l’enregistre avec `theme_queue_hero_critical()` et le système
d’assets l’injecte dans `<style id="critical-css">`.

Ne pas ajouter en parallèle un `<link>` manuel vers ce CSS et ne pas recopier
les styles du hero dans le template PHP.

## JavaScript

`hero()` gère son CSS critique, mais n’initialise pas automatiquement son
JavaScript. Un hero interactif peut déclarer son module dans sa racine :

```php
<header
    class="hero hero-example"
    data-module="heros/hero-example"
    data-context="@visible true">
</header>
```

Le fichier co-localisé exporte alors une initialisation et son nettoyage :

```js
export default function initHeroExample(root) {
    const controller = new AbortController();

    root.addEventListener("click", () => {}, {
        signal: controller.signal,
    });

    return () => controller.abort();
}
```

Pour une animation continue, fournir aussi `pause` et `resume`. Respecter
`prefers-reduced-motion` et ne pas cumuler hydratation et import direct du même
module.

## Créer un hero

Pour `hero('campaign')`, créer :

```text
template-parts/heros/hero-campaign.php
assets/heros/hero-campaign/README.md
assets/heros/hero-campaign/hero-campaign.scss
assets/heros/hero-campaign/hero-campaign.js       # seulement si nécessaire
```

Puis :

1. déclarer le groupe ACF `hero-campaign` si le hero est administrable ;
2. ajouter son import SCSS marqué `critical` dans `assets/app.scss` ;
3. documenter son payload et ses champs ACF dans le README local ;
4. tester l’appel sans argument et avec un payload explicite ;
5. vérifier le `h1`, les dimensions des médias et le comportement LCP ;
6. régénérer les pages avec WP Static si le cache statique est actif.

En développement, le watcher recalcule les entrées dédiées lorsque
`assets/app.scss` change. L'ajout d'un import de hero marqué `critical` ne
nécessite donc pas de relancer manuellement la compilation.

## Heroes disponibles

| Nom | Appel | Documentation |
| --- | --- | --- |
| `homepage` | `hero('homepage')` | [`hero-homepage/README.md`](hero-homepage/README.md) |
| `page` | `hero('page')` | [`hero-page/README.md`](hero-page/README.md) |
| `post` | `hero('post')` | [`hero-post/README.md`](hero-post/README.md) |

## Vérification

Pour un hero contenant une image principale :

- le `<head>` contient le preload de l’image réellement affichée ;
- l’image porte `fetchpriority="high"` et pas `loading="lazy"` ;
- les variantes desktop/mobile utilisent des media queries cohérentes ;
- aucune image secondaire n’est préchargée inutilement ;
- le HTML statique est régénéré après une modification du rendu.
