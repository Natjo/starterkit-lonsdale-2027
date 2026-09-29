# Strates du thème

Les strates sont les sections de page du thème. Elles assemblent des
composants, des cards et, si nécessaire, un module JavaScript.

Pour modifier une strate, lire ce README puis son README local lorsqu’il
existe.

## Appel

```php
strate('text', [
    'title' => 'Titre de la section',
    'text' => '<p>Contenu éditorial.</p>',
]);
```

Le premier argument correspond au suffixe du fichier :

```text
strate('text', [...])
→ template-parts/strates/strate-text.php
→ assets/strates/strate-text/strate-text.scss
→ assets/strates/strate-text/strate-text.js
```

Ne pas inclure le préfixe `strate-` dans l’appel.

Le dispatcher accepte uniquement les lettres, chiffres, tirets et underscores.
Un nom vide ou contenant un segment de chemin est ignoré.

Le second argument est un tableau associatif. `strate()` enregistre les assets,
place ce tableau dans `template_args`, puis charge le template. La strate le
récupère avec :

```php
$args = get_template_args();
```

Le précédent `template_args` est restauré après le rendu. Une strate peut donc
appeler un autre template sans écraser le contexte de son parent.

## Strates ACF

Les strates éditoriales proviennent du flexible content ACF `strates`,
disponible sur la page d'accueil et les contenus du CPT `news`.
`template-parts/utils/dispatch-strates.php` :

1. récupère les champs de la page avec `get_fields()` ;
2. parcourt `strates` ;
3. lit `acf_fc_layout` ;
4. transmet tout le tableau du layout à `strate()`.

Le nom du layout ACF doit correspondre exactement au suffixe du fichier :

```text
acf_fc_layout = text_image
template       = strate-text_image.php
appel          = strate('text_image', $strate)
```

Le dispatcher ignore les entrées invalides et les layouts sans nom.

### Ajouter un layout ACF

1. Définir d’abord son payload et ses champs dans le README local.
2. Repérer dans `acf-json/` le champ `flexible_content` nommé `strates`.
3. Ajouter le layout dans `layouts` avec des clés `layout_*` et `field_*`
   uniques et stables.
4. Faire correspondre exactement le nom du layout au suffixe de la strate et
   les noms des champs aux clés lues dans `$args`.
5. Choisir explicitement les formats de retour attendus par le template :
   identifiant pour une image ou un fichier WordPress, valeur pour un choix.
6. Pour les options communes, ajouter un onglet **Options** puis cloner le
   groupe **Strates - Options** en affichage `seamless`, sans préfixer les noms.
7. Mettre à jour la valeur `modified`, valider le JSON et vérifier que
   WordPress charge le nouveau layout.

Ne pas renommer une clé de layout ou de champ déjà utilisée sans prévoir la
migration des contenus enregistrés. Ne pas recopier les champs du groupe
**Strates - Options** dans chaque layout : utiliser son clone.

## Options communes

La plupart des strates configurables utilisent :

```php
<section <?= options('strate strate-example', $args) ?>>
```

`options()` lit la clé ACF `options` et génère les classes ou l’identifiant de
la section.

Structure reconnue :

```php
'options' => [
    'name' => 'Notre équipe',
    'container' => 'sm',
    'margin' => [
        'top' => 'lg',
        'bottom' => 'sm',
    ],
    'background' => [
        'hasbackground' => true,
        'color' => 'color-2',
        'padding' => [
            'top' => 'lg',
            'bottom' => 'lg',
        ],
    ],
],
```

Effets principaux :

| Clé | Résultat |
| --- | --- |
| `name` | Attribut `id` normalisé |
| `container` | Classe `ctr-{valeur}` |
| `margin.top` | Classe `mt-{valeur}` |
| `margin.bottom` | Classe `mb-{valeur}` ; avec `options`, une valeur vide produit `mb-0` |
| `background.color` | Classe `bg-{valeur}` si le fond est actif |
| `background.padding` | Classes `pt-{valeur}` et `pb-{valeur}` |

Les valeurs `md` correspondent au comportement CSS par défaut et ne génèrent
pas certaines classes utilitaires.

Par exemple, `name => 'Notre équipe'` produit `id="notre-equipe"`. Une valeur
vide ne produit aucun attribut `id`.

Le groupe ACF **Strates - Options** expose les valeurs réellement disponibles :

| Réglage | Valeurs |
| --- | --- |
| Conteneur | défaut, `sm`, `fluid`, `full` |
| Marge haute | aucune, `sm`, `md`, `lg` |
| Marge basse | `0`, `sm`, `md`, `lg` ; défaut `md` |
| Couleur de fond | `color-1`, `color-2`, `color-3` |
| Padding haut | `0`, `xs`, `sm`, `md`, `lg` ; défaut `md` |
| Padding bas | `0`, `sm`, `md`, `lg` ; défaut `md` |

La couleur et les paddings sont affichés seulement lorsque l’arrière-plan est
activé. Les valeurs par défaut correspondent au CSS de `.strate` afin que
l’ajout du groupe ne modifie pas les espacements existants.

## Grille de layout : `full`, `fluid`, `ctr` et `ctr-sm`

Chaque `.strate` utilise la grille globale :

```scss
.strate {
    display: grid;
    grid-template-columns: var(--layout);
    --ctr: fluid;

    > * {
        grid-column: var(--ctr);
    }
}
```

Par défaut, les enfants directs d’une strate occupent donc `fluid`.

### Rails disponibles

| Rail | Zone occupée |
| --- | --- |
| `full` | Toute la largeur de la fenêtre, jusqu’aux bords |
| `fluid` | Largeur disponible moins les gouttières extérieures |
| `ctr` | Conteneur principal, limité par `--ctr-width: 135rem` |
| `ctr-sm` | Conteneur plus étroit que `ctr` sur grand écran |
| `center` | Rail central interne de la grille ; actuellement sans classe utilitaire et non utilisé par les strates |

Les gouttières extérieures viennent de `--ctr-offset` :

- `1.6rem` sous `768px` ;
- `6rem` à partir de `768px`.

Sous `768px`, `fluid`, `ctr`, `ctr-sm` et `center` partagent la même zone
intérieure. `full` reste le seul rail allant jusqu’aux bords de la fenêtre.

### Classes de conteneur sur la strate

La clé `options.container` produit une classe `ctr-{valeur}`. Les classes
réellement disponibles sont :

| Valeur ACF | Classe produite | Effet |
| --- | --- | --- |
| absente | aucune | La strate conserve son défaut `fluid` |
| `fluid` | `ctr-fluid` | Force `--ctr: fluid` |
| `full` | `ctr-full` | Force `--ctr: full` |
| `sm` | `ctr-sm` | Force `--ctr: ctr-sm` à partir de `1025px`; reste `fluid` en dessous |

Il n’existe pas actuellement de classe `.ctr-ctr`. Pour placer une zone sur le
rail `ctr`, le SCSS de la strate doit utiliser explicitement :

```scss
.strate-example {
    .strate-content {
        grid-column: ctr;
    }
}
```

### Mélanger plusieurs largeurs dans une strate

Si tous les enfants utilisent la même largeur, laisser `.strate-content` comme
enfant direct et choisir son rail :

```scss
.strate-example .strate-content {
    grid-column: ctr;
}
```

Si une même strate contient des zones de largeurs différentes, son conteneur
doit couvrir `full` et réutiliser la grille parente avec `subgrid` :

```scss
.strate-example {
    .strate-content {
        display: grid;
        grid-template-columns: subgrid;
        grid-column: full;
        min-width: 0;
    }

    .strate-header {
        grid-column: ctr;
    }

    .media-full {
        grid-column: full;
    }

    .content-fluid {
        grid-column: fluid;
    }
}
```

Cette structure permet par exemple de garder un titre dans `ctr` tout en
affichant un marquee bord à bord dans `full`. Ajouter `min-width: 0` sur les
conteneurs de grille qui hébergent un slider, un marquee ou un média large afin
d’éviter un débordement intrinsèque.

## Squelette d’une strate configurable

```php
<?php
$args = get_template_args();
?>

<section <?= options('strate strate-example', $args) ?>>
    <div class="strate-content">
        <?php component('header', [
            'title' => $args['title'] ?? '',
            'text' => $args['text'] ?? '',
            'classes' => 'strate-header',
        ]); ?>

        <?php component('list', [
            'items' => $args['items'] ?? [],
            'card' => 'news',
        ]); ?>
    </div>
</section>
```

Règles :

- la strate possède la balise `<section>` et sa structure de layout ;
- les éléments réutilisables sont rendus avec `component()` ou `card()` ;
- transmettre des payloads explicites plutôt que le tableau complet lorsque
  les clés attendues diffèrent ;
- filtrer et normaliser les options propres à la strate ;
- laisser chaque composant gérer son échappement ;
- fournir les libellés variables depuis la strate ou l’appelant, sans ajouter
  de clés de traduction dans les composants ;
- documenter le payload public dans un README local.

## Strate statique

Une strate de démonstration ou de styleguide peut être entièrement statique :

```php
strate('formulaire');
```

Elle doit indiquer dans son README qu’elle ignore les arguments transmis. Une
strate éditoriale destinée à ACF doit rester configurable.

## Hydratation et propriété du JavaScript

Une seule couche doit posséder l’interactivité.

Si la strate ne fait qu’afficher un composant autonome, le composant peut
conserver son hydratation.

Si la strate pilote le cycle de vie du composant, elle désactive son hydratation
et porte elle-même le module :

```php
<section
    <?= options('strate strate-slider', $args) ?>
    data-module="strates/strate-slider"
    data-context="@visible true">

    <?php component('slider', [
        'items' => $args['items'] ?? [],
        'hydrate' => false,
    ]); ?>
</section>
```

Le JavaScript de la strate importe alors le contrôleur du composant et retourne
son nettoyage :

```js
import { Slider } from '@components/slider/slider';

export default function initStrateSlider(root) {
    const element = root.querySelector('.slider');
    if (!element) return;

    const slider = new Slider(element);
    slider.add();

    return () => slider.remove();
}
```

Pour un module suspendable hors écran, retourner `destroy`, `pause` et `resume`.
Le chargeur d’hydratation appelle automatiquement ces méthodes.

Une strate qui initialise plusieurs modules peut retourner directement leurs
lifecycles :

```js
export default function initStrateExample(root) {
    return [
        initAnimation(root),
        initSlider(root),
        initOtherModule(root),
    ];
}
```

Chaque entrée peut être une fonction de nettoyage, un objet lifecycle partiel
ou `undefined`. L'hydrateur appelle les `pause()` et `resume()` disponibles et
les `destroy()` dans l'ordre inverse. Un module qui travaille continuellement
doit fournir à la fois `pause()` et `resume()`.

## Fichiers et assets

Structure complète :

```text
template-parts/strates/strate-<nom>.php
assets/strates/strate-<nom>/README.md
assets/strates/strate-<nom>/strate-<nom>.scss
assets/strates/strate-<nom>/strate-<nom>.js
```

Le PHP est obligatoire. Le README, le SCSS et le JavaScript dépendent du
comportement de la strate. Les imports SCSS actifs sont déclarés dans
`assets/app.scss`.

## Strates disponibles

| Strate | État actuel |
| --- | --- |
| `accordion` | Assemble le composant `accordion` |
| `formulaire` | Strate statique réservée au styleguide |
| `intro` | Squelette vide |
| `keynumbers` | Assemble une liste de `card-keynumber` |
| `marquee` | Assemble et pilote `marquee` |
| `media` | Affiche une image ou une vidéo |
| `paralax` | Strate statique plein écran avec image animée au scroll |
| `pushes` | Sélection manuelle ou remontée automatique de contenus |
| `quote` | Assemble le composant `quote` |
| `slider` | Assemble et pilote `slider` |
| `text` | Assemble `title` et `text` |
| `text_animated` | Strate statique réservée au styleguide, pilotée par `textAnimated` |
| `text_image` | Assemble `title`, `text` et `picture` |

Ne pas considérer un template vide comme une strate terminée. Avant de
l’utiliser, vérifier son PHP et son README local.

## Documentation locale

Créer un README local dès qu’une strate possède un payload, des options, des
champs ACF ou une hydratation spécifiques. Il doit contenir :

- un exemple d’appel ;
- le tableau des clés, types et valeurs par défaut ;
- les noms de champs ACF ;
- les composants et cards assemblés ;
- le propriétaire de l’hydratation ;
- les cas produisant une strate vide ;
- les limites connues.

Le README local est la source de vérité de la strate. Une IA doit le lire avant
de modifier son template ou ses assets.
