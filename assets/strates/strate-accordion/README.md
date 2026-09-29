# Strate Accordion

Affiche un titre, un texte d’introduction et une liste de contenus repliables.

## Appel

```php
strate('accordion', [
    'title' => 'Questions fréquentes',
    'text' => '<p>Introduction.</p>',
    'items' => [
        [
            'title' => 'Question',
            'text' => '<p>Réponse.</p>',
        ],
    ],
]);
```

## Contrat

| Clé | Type | Rôle |
| --- | --- | --- |
| `title` | `string` | Titre de la strate |
| `text` | `string` | Introduction éditoriale |
| `items` | `array` | Éléments transmis à `component('accordion')` |
| `items[].title` | `string` | Libellé de l’élément |
| `items[].text` | `string` | Contenu déplié |

Le manifeste fournit un contenu Lorem et trois éléments de démonstration. Forge
les utilise uniquement pour préremplir une nouvelle instance.

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])` ;
- `component('accordion', [...])`.
