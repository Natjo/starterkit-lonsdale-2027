# Hero `news`

Affiche l’en-tête éditorial d’une actualité.

## Appel

Sans second argument, le helper charge le groupe ACF `hero-news` :

```php
hero('news');
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `date` | `mixed` | `''` | Transmis à `component('date')` |
| `title` | `mixed` | `''` | Transmis à `component('title')` |
| `images` | `array` | `[]` | Transmis à `component('picture')` |

Le titre est rendu en `h1`. Le groupe `images` accepte les variantes `desktop`
et `mobile` configurées dans ACF.

## Dépendances

- `component('date', [...])` ;
- `component('title', [...])` ;
- `component('picture', [...])`.
