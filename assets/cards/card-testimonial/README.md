# Card `card-testimonial`

Affiche un témoignage en s’appuyant sur le composant `quote`.

```php
card('testimonial', [
    'quote' => '<p>Une collaboration remarquable.</p>',
    'image' => 123,
    'image_size' => 'full',
    'author' => 'Jane Doe',
    'source' => 'Example Company',
]);
```

`quote` est obligatoire. La card transmet l’image, l’auteur et la source au
composant `quote`. La source est toujours rendue comme du texte, sans lien. Le
composant filtre le HTML de la citation et échappe les libellés.

## Contribution ACF

Le groupe **Card - testimonial** apparaît sur les fiches du CPT Testimonial.
Ses champs sont imbriqués dans `card` :

| Champ | Chemin | Type |
| --- | --- | --- |
| Citation | `card.quote` | WYSIWYG Basic, obligatoire |
| Portrait | `card.image` | Image, retour ID |
| Auteur | `card.author` | Texte |
| Source | `card.source` | Texte |

La card accepte le groupe imbriqué et les payloads plats existants. Un
témoignage publié peut être rendu par ID même si son CPT n’expose pas de single
ou d’archive. Les statuts privés, brouillons et les contenus protégés restent
exclus. Aucun contenu de démonstration n’est enregistré automatiquement.

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
