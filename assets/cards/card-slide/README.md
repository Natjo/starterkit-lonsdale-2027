# Card `card-slide`

Card éditoriale utilisée par `slider-cards`, sans publication WordPress.

```php
card('slide', [
    'title' => 'Notre engagement',
    'text' => '<p>Une description courte.</p>',
    'image' => 123,
    'link' => ['url' => '/engagement/', 'title' => 'Découvrir'],
]);
```

`title`, `text`, `image` et `link` sont facultatifs, mais la strate ignore une
ligne où les trois contenus visibles sont vides. Un lien valide transforme la
card en lien ; sans URL, elle conserve un conteneur non interactif. L’image
ACF est attendue sous forme d’identifiant WordPress.
