# Hero `page`

Affiche le titre et le texte éditorial d’une page interne.

## Appel

Sans second argument, le helper charge le groupe ACF `hero-page` :

```php
hero('page');
```

Un payload explicite remplace la lecture ACF :

```php
hero('page', [
    'title' => 'Titre de la page',
    'text' => '<p>Introduction de la page.</p>',
]);
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `mixed` | `''` | Transmis à `component('title')` |
| `text` | `mixed` | `''` | Transmis à `component('text')` |

Le titre est rendu en `h1` avec la classe `title-1`. Le template ne produit pas
de contenu de démonstration.

## ACF

| Nom | Identifiant | Type | Format |
| --- | --- | --- | --- |
| Titre | `title` | `textarea` | Texte |
| Texte | `text` | `wysiwyg` | HTML filtré |

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])`.
