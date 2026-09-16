# Card `card-team`

Affiche un membre de l’équipe depuis un post WordPress ou un payload.

```php
card('team', [
    'title' => 'Jane Doe',
    'text' => '<p>Creative director</p>',
    'image' => 123,
    'image_size' => 'large',
    'link' => ['url' => '/team/jane-doe/', 'title' => 'Jane Doe'],
]);
```

`link.url` transforme le contenu de la card en lien. `title`, `text`, `image`
et `image_size` sont transmis aux composants correspondants.
