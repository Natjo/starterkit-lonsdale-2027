# Strate Intro

Affiche un court texte éditorial d’introduction.

## Appel

```php
strate('intro', [
    'text' => '<p>Lorem ipsum dolor sit amet.</p>',
]);
```

## Contrat

| Clé | Type | Défaut dans ACF | Rôle |
| --- | --- | --- | --- |
| `text` | `string` | Paragraphe Lorem ipsum | Contenu transmis à `component('text')` |

La valeur de démonstration est définie par `default_value` dans le layout ACF.
Forge l’utilise pour préremplir une nouvelle instance de la strate sans écraser
une contribution existante.

## Dépendances

- `component('text', [...])`.

La strate ne produit aucun HTML sans contenu exploitable. Les champs absents
sont traités comme vides.
