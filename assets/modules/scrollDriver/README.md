# Module `ScrollDriver`

Moteur léger d'animations pilotées par le scroll, sans GSAP. Il centralise les
mesures, l'`IntersectionObserver`, le listener de scroll et le
`requestAnimationFrame` pour tous ses consommateurs.

Lire ce README avant d'ajouter une animation ou de modifier son cycle de vie.

## API publique

```js
import {
    ScrollDriver,
    scrollAnimation,
    stagger,
    style,
} from "@modules/scrollDriver/scrollDriver";
```

| Export | Rôle |
| --- | --- |
| `ScrollDriver()` | Retourne le singleton partagé de la page |
| `scrollAnimation()` | Crée un lifecycle hydratable `{ pause, resume, destroy }` |
| `stagger(root, progress, options)` | Distribue une progression sur des enfants avec des variables CSS |
| `style.var()` | Écrit une variable CSS en évitant les écritures identiques |
| `style.clear()` | Retire les variables écrites par `style` et réinitialise le cache de `stagger` |

Les anciennes API `SectionAnimate`, `snap`, `cubicBezier`, `upDown`,
`timelineInOut`, `toggle`, `trigger` et les writers de styles génériques ont
été retirés. Ne pas les réintroduire sans consommateur réel.

## Utilisation recommandée dans une strate

```js
import {
    scrollAnimation,
    style,
} from "@modules/scrollDriver/scrollDriver";

export default function initStrateExample(root) {
    return scrollAnimation(root, {
        range: [85, 40],
        update(event) {
            event.timeline(0, 100, progress => {
                style.var(root, progress, "--progress");
            });
        },
    });
}
```

Le helper inscrit immédiatement la strate et retourne le contrat attendu par
`hydrate`. Hors écran, `pause()` la retire du driver. À son retour,
`resume()` la réinscrit sans doublon. `destroy()` la retire définitivement et
appelle automatiquement `style.clear(root)`, puis le nettoyage spécifique
fourni une seule fois.

Lorsque `prefers-reduced-motion: reduce` est actif, le helper retourne
`undefined` sans inscrire l'élément dans le driver. Les modules qui modifient
le DOM avant d'appeler `scrollAnimation()` doivent toutefois vérifier cette
préférence avant leur propre initialisation.

Options :

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `range` | `string|[number, number]` | `"top-bottom"` | Type ou plage de progression |
| `update` | `function` | — | Callback reçu par `driver.add()` ; obligatoire |
| `destroy` | `function` | `undefined` | Nettoyage spécifique exécuté après le nettoyage automatique des variables CSS |

Une cible invalide ou un callback `update` absent retourne `undefined` sans
activer le driver.

## Utilisation bas niveau

```js
const driver = ScrollDriver();

driver.add(section, [85, 40], event => {
    event.timeline(0, 100, progress => {
        // progress est compris entre 0 et 1.
    });
});

driver.enable();

return () => driver.remove(section);
```

Toujours appeler `remove()` dans le nettoyage du module propriétaire. Ne pas
appeler `destroy()` depuis un composant : le singleton peut être partagé avec
d'autres éléments de la page.

Préférer `scrollAnimation()` dans une strate hydratée. L'API bas niveau reste
utile aux modules ayant plusieurs animations ou un cycle particulier, comme le
footer.

## Cycle de vie du singleton

### `driver.add(element, type, animation)`

Enregistre un élément et retourne son contrôleur interne. L'appel est ignoré
si le driver est détruit ou si la cible n'est pas un élément DOM.

Une cible ne peut avoir qu'une inscription : un nouvel `add()` sur le même
élément remplace proprement la précédente.

```js
driver.add(element, "bottom-bottom", event => {
    event.timeline(80, 100, progress => {
        // progression 0..1 sur la fin de la plage.
    });
});
```

### `driver.enable()`

Active le driver de manière idempotente :

- mesure toutes les sections et applique leur état initial ;
- ajoute le listener `resize` ;
- utilise le bridge Lenis partagé lorsque `window.lenis` existe ;
- utilise temporairement le scroll natif sinon ;
- bascule automatiquement vers Lenis lors de l'événement `lenis:ready`.

### `driver.remove(element)`

Désobserve l'élément, retire `.viewed`, supprime ses références et appelle
`release()`. Quand la dernière section disparaît, le driver est désactivé :

- listeners scroll, resize et `lenis:ready` retirés ;
- listener Lenis retiré de l'instance exacte qui l'avait reçu ;
- RAF en attente annulé.

Le singleton vide reste réutilisable.

### `driver.refresh()`

Remesure toutes les sections et réapplique immédiatement leur état au scroll
courant. À appeler après un changement de layout qui ne déclenche pas de
`resize`, par exemple après l'ouverture d'un panneau ou l'injection d'un média.

```js
driver.refresh();
```

### `driver.disable()` et `driver.destroy()`

`disable()` suspend les listeners sans supprimer les sections. Un prochain
`enable()` les remesure et les réactive.

`destroy()` est réservé au teardown global : il désactive le driver,
déconnecte son observer et vide toutes ses sections. Le prochain appel à
`ScrollDriver()` créera une nouvelle instance.

### `driver.onScroll(scrollY)`

Planifie une mise à jour avec une position explicite. Cette méthode sert au
bridge Lenis ; les composants n'ont normalement pas à l'appeler. Elle ne fait
rien lorsque le driver est désactivé ou détruit.

## Sections actives et performance

Chaque section est observée avec une marge de `100px`. Après l'état initial,
seules les sections proches du viewport sont recalculées pendant le scroll.
Quand aucune section n'est active, aucun callback d'animation n'est exécuté.

Le driver utilise un RAF partagé et fusionne les événements reçus pendant une
même frame. Un `resize` force une mesure et une mise à jour de toutes les
sections sur la frame suivante. Plusieurs événements `resize` reçus avant
cette frame ne provoquent qu'une seule série de mesures.

## Types de progression

Le deuxième argument de `add()` détermine le calcul de `event._percent` :

| Type | Plage équivalente | Usage |
| --- | --- | --- |
| `"top-bottom"` | `[0, 100]` | Type par défaut |
| `"top-top"` | `[0, 0]` | Du haut de l'élément au bas de l'élément, alignés en haut |
| `"middle-middle"` | `[50, 50]` | Traversée du milieu du viewport |
| `"bottom-bottom"` | `[100, 100]` | Traversée du bas du viewport |
| `"middle-bottom"` | `[50, 100]` | Du milieu vers le bas du viewport |
| `"bottom-top"` | `[100, 0]` | Traversée complète du viewport |
| `[start, end]` | — | Positions personnalisées du viewport en pourcentage |

Les presets sont uniquement des alias lisibles. Tous passent par la même
formule générique que les tableaux.

Exemple `[85, 40]` : la plage commence lorsque le haut de l'élément atteint
environ `85%` du viewport et se termine lorsque son bas atteint `40%`.

## API du callback `event`

### `event.timeline(start, end, callback)`

Découpe la progression de la section entre `start` et `end`. La valeur passée
au callback est toujours comprise entre `0` et `1`.

```js
event.timeline(20, 80, progress => {
    element.style.opacity = String(progress);
});
```

Plusieurs timelines peuvent être appelées dans le même `update`. Le contrôle
des limites est calculé une fois par mise à jour : toutes reçoivent donc leur
valeur finale `0` ou `1` lorsque la section quitte sa plage.

## `stagger()`

```js
stagger(root, progress, {
    selector: ".char",
    softness: 6,
});
```

`progress` accepte `0..1` ou `0..100`. Pour un élément racine, les enfants
sont mis en cache avec leurs racines et leurs options. Les index, le
dénominateur et la souplesse sont préparés une seule fois ; pendant le scroll,
seule la progression est recalculée et écrite sur la racine :

| Variable | Cible | Rôle |
| --- | --- | --- |
| `--p` | Racine | Progression normalisée `0..1` |
| `--denom` | Racine | Nombre de segments |
| `--softness` | Racine si fournie | Souplesse de la propagation |
| `--i` | Chaque enfant | Index écrit une seule fois |

Sélecteur par défaut : `[data-progress-item], .char, .item`.

Préférer une racine DOM à un tableau d'éléments. Une racine produit des
écritures O(1) par frame ; un tableau sans racine commune écrit les variables
sur chaque élément.

### Nettoyage de `stagger`

```js
style.clear(root);
```

Cela retire les variables suivies sur la racine et ses descendants concernés,
puis invalide les caches pour permettre une future réinitialisation.
