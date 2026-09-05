# Composant `section-snap`

Produit la navigation `.snap-nav` d’un groupe de sections et transforme les
sections sœurs en étapes verticales pilotées par le Lenis global.

## Appel

Le composant doit être placé directement dans le conteneur qui possède les
slides :

```php
<?php
$root_id = wp_unique_id('snap-');
$slides = [
    ['title' => 'Introduction'],
    ['title' => 'Expertises'],
];
?>

<section class="strate strate-snap">
    <?php component('section-snap', [
        'slides' => $slides,
        'root_id' => $root_id,
    ]); ?>

    <?php foreach ($slides as $index => $slide) : ?>
        <section
            id="<?= esc_attr($root_id . '-' . ($index + 1)) ?>"
            class="strate">
        </section>
    <?php endforeach; ?>
</section>
```

Les slides contrôlées doivent être des enfants directs du même parent, porter
la classe `.strate` et posséder les IDs générés à partir de `root_id`.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `slides` | `array` | `[]` | Liste de slides associatives |
| `slides[].title` | `scalar\|Stringable` | — | Libellé obligatoire de l’ancre |
| `root_id` | `scalar\|Stringable` | `''` | Préfixe commun des IDs, obligatoire et sécurisé |
| `aria_label` | `scalar\|Stringable` | `Navigation des sections` | Nom accessible du `<nav>` |
| `classes` | `string\|array` | `''` | Classes supplémentaires de la navigation |
| `attributes` | `array` | `[]` | Attributs supplémentaires de la navigation |

Le composant ne produit aucun HTML sans `root_id` valide ou sans titre valide.
Les titres sont échappés avec `esc_html()` et les fragments avec `esc_attr()`.
`class`, `aria-label`, `data-module` et `data-context` sont réservés.

## Hydratation

La navigation porte `data-module="components/section-snap"`. Le module prend
son parent direct comme racine et utilise :

```js
sectionSnap(root, {
    slideSelector: ":scope > .strate",
    navSelector: ":scope > .snap-nav",
    activationOffset: "var(--header-height)",
    deactivationOffset: "var(--header-height)",
    breakpoint: 1025,
});
```

Sous `1025px`, le snap et ses variables de progression sont désactivés. Le
composant respecte `prefers-reduced-motion` et fonctionne sans Lenis en laissant
le scroll natif disponible.

## Comportement

- molette et clavier pour atteindre la section précédente ou suivante ;
- snap vers la cible la plus proche après un scroll manuel ;
- clic des ancres avec Lenis ou `scrollIntoView()` ;
- lien actif synchronisé avec `active` et `aria-current="location"` ;
- publication de `--cursor-top` et `--cursor-height` sur `.snap-nav` ;
- publication de `--i` et `--p` sur les slides ;
- émission de `snap:change` sur le conteneur avec `{ index, slide }` ;
- respect des contenus scrollables et des attributs `data-snap-ignore`,
  `data-lenis-prevent` et `data-lenis-prevent-wheel`.

Le lifecycle `{ pause, resume, destroy }` nettoie listeners, observers, timers,
transitions Lenis, états actifs et variables CSS inline.
