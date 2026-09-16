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
