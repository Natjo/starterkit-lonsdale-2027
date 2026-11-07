# Hero `post`

Affiche l’en-tête éditorial d’une publication (article ou CPT).

## Appel

Sans second argument, le helper charge le groupe ACF `hero-post` :

```php
hero('post');
```

## Contrat

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `date` | `mixed` | `''` | Transmis à `component('date')` |
| `title` | `mixed` | `''` | Transmis à `component('title')` |
| `images` | `array` | `[]` | Transmis à `component('picture')` |

Le titre est rendu en `h1`. Le groupe `images` accepte les variantes `desktop`
et `mobile` configurées dans ACF.

## Taxonomies

Le hero récupère les termes associés à la publication WordPress courante pour
chacune de ses taxonomies publiques.
Chaque terme est affiché avec `component('tag')`, entre le titre et l’image.
Ces tags sont informatifs, sans lien. Les taxonomies internes sont ignorées.
Sans terme ou en cas d’erreur WordPress, aucun conteneur de tags n’est rendu.
Aucun champ ACF supplémentaire n’est nécessaire.

## Dépendances

- `component('tag', [...])` ;
- `component('date', [...])` ;
- `component('title', [...])` ;
- `component('picture', [...])`.

Les données précédemment enregistrées sous `hero-news` restent accessibles
via un repli de lecture ACF. Les prochaines sauvegardes utilisent `hero-post`.

Dans le starter, la règle de localisation ACF reste affectée au CPT News.
Elle peut être adaptée aux autres types de publication qui utilisent ce hero.
