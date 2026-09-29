# Strate Text

Affiche un titre et un contenu éditorial riche.

## Appel

```php
strate('text', [
    'title' => 'Lorem ipsum dolor sit amet',
    'text' => '<p>Contenu éditorial.</p>',
]);
```

## Contrat

| Clé | Type | Rôle |
| --- | --- | --- |
| `title` | `string` | Titre de la strate |
| `text` | `string` | HTML éditorial transmis à `component('text')` |

Le contenu de démonstration défini dans ACF présente les formats disponibles :

- niveaux visuels `h1`, `h2`, `h3` et `h4` ;
- texte `sm`, `lg`, `xl` et `wd` ;
- gras, italique, souligné et lien ;
- citation ;
- listes à puces et numérotées.

Forge reprend ces `default_value` uniquement lors de l’initialisation d’une
nouvelle strate.

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])`.
