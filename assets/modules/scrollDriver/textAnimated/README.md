# Module `textAnimated`

Anime progressivement la couleur des caractères d'un titre en fonction du
scroll. Le texte est découpé avec `splitText`, puis sa progression est pilotée
par le singleton `ScrollDriver`.

Ce module est indépendant de `textAppear` : il suit continuellement la position
de scroll dans une plage donnée au lieu de jouer une animation d'entrée unique.

## Statut actuel

Le SCSS est inclus dans le bundle critique par `assets/app.scss`. Le module
JavaScript est notamment utilisé par la strate statique `text-animated` du
catalogue. La classe `.text-animated` seule ne lance pas le comportement : le
module doit toujours être initialisé par son propriétaire.

Le chargeur `hydrate` ne charge pas directement les chemins `modules/...`.
Importer `textAnimated` dans le fichier JavaScript du hero, du composant ou de
la strate qui l'utilise.

## Dépendances

- [`ScrollDriver`](../scrollDriver.README.md) pour mesurer la progression et
  partager les listeners de scroll, resize et Lenis ;
- [`splitText`](../../splitText/splitText.README.md) pour créer les `.word` et
  `.char` et restaurer le DOM sans perdre les éléments inline.

## Markup attendu

Le module cherche uniquement le premier descendant `.text-animated` de sa
racine :

```html
<section class="strate strate-example">
    <h2 class="title text-animated">Un titre animé au scroll</h2>
</section>
```

Sans élément `.text-animated`, le module ne fait rien.

## Utilisation

Depuis le JavaScript d'une strate :

```js
import textAnimated from "@modules/scrollDriver/textAnimated/textAnimated";

export default function initExample(root) {
    return textAnimated(root);
}
```

Le template de la strate porte l'hydratation :

```php
<section
    class="strate strate-example"
    data-module="strates/strate-example"
    data-context="@visible true">
    <h2 class="title text-animated">Titre</h2>
</section>
```

Ne pas utiliser `data-module="modules/scrollDriver/textAnimated"` : ce chemin
ne fait pas partie des familles reconnues par l'hydrateur.

## Déroulement

| Étape | Comportement |
| --- | --- |
| Recherche | Sélectionne le premier `.text-animated` dans `root` |
| Mouvement réduit | Quitte immédiatement, sans split ni listener |
| Attente | Observe le titre avec `IntersectionObserver`, marge `100px` |
| Initialisation | Déconnecte l'observer et appelle `splitText(title)` |
| Scroll | Enregistre le titre dans le singleton `ScrollDriver` |
| Progression | Applique `stagger(title, progress)` entre `0` et `1` |
| Nettoyage | Retire le titre du driver, efface les styles calculés et restaure le texte |

L'observer initial évite de créer tous les spans tant que le titre est loin du
viewport. L'observer interne de `ScrollDriver` limite ensuite les mises à jour
aux sections proches de l'écran et ajoute la classe `.viewed`.

## Plage de scroll

Le module utilise :

```js
driver.add(title, [85, 40], callback);
```

- progression `0` lorsque le haut du titre atteint environ `85%` de la hauteur
  du viewport ;
- progression `100` lorsque le bas du titre atteint environ `40%` du viewport.

Cette plage est actuellement fixe et ne fait pas partie d'une option publique.

## Variables CSS

`stagger()` écrit les variables suivantes :

| Variable | Élément | Rôle |
| --- | --- | --- |
| `--p` | `.text-animated`, puis hérité | Progression globale normalisée entre `0` et `1` |
| `--denom` | `.text-animated`, puis hérité | Nombre de segments, basé sur le nombre de caractères |
| `--softness` | `.text-animated`, puis hérité | Souplesse de propagation ; défaut SCSS actuel : `6` |
| `--i` | Chaque `.char` | Index du caractère |
| `--progress` | Chaque `.char` | Progression calculée en CSS pour ce caractère |

Le rendu actuel interpole la couleur de chaque caractère entre un blanc à
`25%` et un blanc opaque avec `color-mix()`.

## API et cycle de vie

```js
const lifecycle = textAnimated(root);

lifecycle?.pause();
lifecycle?.resume();
lifecycle?.destroy();
```

Le module retourne `undefined` lorsque :

- la racine ne contient pas `.text-animated` ;
- `prefers-reduced-motion: reduce` est actif.

Dans les autres cas, il retourne `{ pause, resume, destroy }` :

1. `pause()` déconnecte l'observer initial ou retire le titre du driver ;
2. `resume()` reprend l'observation ou réinscrit le titre sans doublon ;
3. `destroy()` déconnecte l'observer, retire le titre du driver, laisse
   `scrollAnimation()` effacer les variables puis appelle `restore()` pour
   remettre le DOM d'origine.

Ce lifecycle peut être transmis directement à `hydrate` par le module parent.

## Mouvement réduit

Lorsque `prefers-reduced-motion: reduce` est actif, le JavaScript quitte avant
de découper le texte. Le contenu original reste donc intact et aucun listener
supplémentaire n'est activé par ce module.

Le SCSS contient aussi un fallback qui retire `will-change` si une préférence
de mouvement réduit est activée après l'initialisation.

## Performance

- le split DOM est différé avec `IntersectionObserver` ;
- `ScrollDriver` est partagé entre toutes les animations de la page ;
- le titre est retiré du driver lorsque l'hydrateur le met en pause hors écran ;
- les index `--i` ne sont initialisés qu'une fois sur les caractères ;
- `--p` et `--denom` sont écrites uniquement sur le titre à chaque frame ;
- aucune boucle RAF propre n'est créée par `textAnimated` ;
- lorsque le dernier élément est retiré du driver, ses listeners globaux sont
  automatiquement désactivés.

Les spans restent présents pendant toute la durée de vie du module. Toujours
retourner son lifecycle dans un contexte hydraté ou dynamique.

## Limites actuelles

- un seul `.text-animated` est pris en charge par racine ;
- la plage `[85, 40]`, la douceur et les couleurs ne sont pas configurables par
  l'API JavaScript ;
- `IntersectionObserver` est requis dans l'implémentation actuelle ;
- les couleurs SCSS actuelles sont adaptées à un fond sombre ;
- le module ne se relance pas automatiquement lorsqu'un nouveau titre est
  injecté dans la même racine.

## Différence avec `textAppear`

| | `textAnimated` | `textAppear` |
| --- | --- | --- |
| Déclencheur | Progression du scroll | Animation d'entrée |
| Cible | Premier `.text-animated` | Plusieurs blocs configurés |
| Durée | Tant que la section traverse la plage | Une animation CSS |
| Moteur | `ScrollDriver` + `stagger` | Double `requestAnimationFrame` |
| Split différé | Toujours, avec observer | Option `whenVisible` |
| Nettoyage | Retire la section du driver | Annule observer/RAF et restaure les blocs |
