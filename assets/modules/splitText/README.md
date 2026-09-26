# Module `splitText`

Découpe les nœuds texte d'un élément en spans de mots et de caractères afin de
permettre des animations individuelles, tout en conservant le HTML inline.

Dans le catalogue Forge, l'ajout de ce module active également `scrollDriver`.
L'ajout de `scrollDriver` seul n'active pas `splitText`.

## Utilisation

```js
import { splitText } from "@modules/splitText/splitText";

const { words, chars, restore } = splitText(title);

chars.forEach((char, index) => {
    char.style.setProperty("--i", index);
});

restore();
```

Structure produite :

```html
<span class="word">
    <span class="char">T</span>
    <span class="char">e</span>
    <span class="char">x</span>
    <span class="char">t</span>
    <span class="char">e</span>
</span>
```

## Options

| Option | Défaut | Description |
| --- | --- | --- |
| `wordClass` | `word` | Classe du span enveloppant un mot |
| `charClass` | `char` | Classe de chaque graphème |
| `skipSelector` | chaîne vide | Ignore les nœuds texte placés dans un élément correspondant |

Une classe vide ou invalide reprend sa valeur par défaut. Un sélecteur CSS
invalide est ignoré sans interrompre l'initialisation.

## Contrat

- le HTML inline et les éléments existants restent en place ;
- les listeners et références des descendants sont préservés ;
- les espaces originaux sont conservés sous forme de nœuds texte ;
- les caractères Unicode complexes utilisent `Intl.Segmenter` lorsqu'il est
  disponible, avec `Array.from()` comme fallback ;
- un `<br>` produit un espace dans le libellé accessible ;
- un `aria-label` existant est conservé puis restauré à l'identique ;
- un second split restaure d'abord le précédent afin d'éviter les spans
  imbriqués ;
- `restore()` est idempotente et retire uniquement les nœuds générés par le
  split concerné ;
- une cible invalide retourne des tableaux vides et un nettoyage sans effet.

## Nettoyage

Toujours appeler ou retourner `restore()` lorsque le composant est détruit :

```js
export default function initExample(root) {
    const title = root.querySelector(".title");
    if (!title) return;

    const { restore } = splitText(title);
    return restore;
}
```

Les modules `textAppear` et `scrollDriver/textAnimated` appliquent déjà ce
nettoyage dans leur cycle de vie.
