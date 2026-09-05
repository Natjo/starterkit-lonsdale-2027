# Module `numberCounter`

Anime un ou plusieurs nombres de `0` jusqu'à leur valeur affichée. Le module
est indépendant des composants et des strates.

## Utilisation

```js
import numberCounter from "@modules/numberCounter/numberCounter";

const lifecycle = numberCounter(
    root.querySelectorAll(".value"),
    { duration: 1600 },
);
```

Le premier argument accepte un élément ou un itérable d'éléments. Le texte de
chaque élément constitue la valeur finale.

## Formats acceptés

Le module anime les entiers, les décimales et les valeurs négatives :

```text
1250
1 250
1'250
12,5
-42
```

Les espaces, espaces insécables et apostrophes utilisés comme séparateurs de
milliers sont conservés. Le séparateur décimal et le nombre de décimales sont
également conservés. À la fin, le texte original exact est restauré.

Une valeur non numérique est ignorée sans être modifiée.

## Options

| Option | Type | Défaut | Rôle |
| --- | --- | ---: | --- |
| `duration` | nombre positif | `1600` | Durée du comptage en millisecondes. |

Une durée absente, non numérique, nulle ou négative utilise la valeur par
défaut. La progression emploie une courbe `easeOutCubic` pour ralentir à
l'approche de la valeur finale.

## Nettoyage

Le module démarre immédiatement et retourne une fonction de nettoyage
compatible avec l'hydrateur :

```js
const destroy = numberCounter(elements);
destroy?.();
```

Le nettoyage annule la frame éventuelle et restaure les textes originaux. Il
est idempotent. L'animation étant courte et jouée une seule fois, elle n'expose
pas de méthodes `pause` ou `resume`.

Si aucune valeur n'est exploitable ou si `prefers-reduced-motion: reduce` est
actif, le module ne modifie pas le DOM et ne crée aucune frame.

## Intégration dans une strate hydratée

Le chargement à l'entrée du viewport reste la responsabilité de la strate :

```php
<section data-module="strates/strate-example" data-context="@visible true">
```

```js
import numberCounter from "@modules/numberCounter/numberCounter";

export default function initExample(root) {
    return numberCounter(root.querySelectorAll(".value"));
}
```

La strate retourne directement la fonction de nettoyage afin que l'hydrateur
puisse détruire proprement le compteur.
