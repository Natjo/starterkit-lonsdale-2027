# Card `card-testimonial`

Affiche un témoignage en s’appuyant sur le composant `quote`.

```php
card('testimonial', [
    'quote' => '<p>Une collaboration remarquable.</p>',
    'image' => 123,
    'image_size' => 'full',
    'author' => 'Jane Doe',
    'source' => 'Example Company',
    'url' => 'https://example.com/',
]);
```

`quote` est obligatoire. `url` est l’URL facultative de `source` : elle ne
rend un lien que lorsque la source et l’URL sont toutes les deux renseignées.
Le composant `quote` filtre le HTML de la citation, échappe les libellés et
sécurise l’URL.
