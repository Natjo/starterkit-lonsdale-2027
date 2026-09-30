# Strate `logos`

Affiche une grille statique de logos de partenaires ou clients, sans animation.

```php
strate('logos', [
    'title' => 'Nos partenaires',
    'logos' => [
        [
            'name' => 'Partenaire',
            'image' => 123,
            'link' => ['url' => 'https://example.com', 'title' => 'Site du partenaire'],
        ],
    ],
]);
```

Le layout ACF propose un titre et un texte d'introduction facultatifs, puis
un repeater `logos`. Chaque ligne demande une image ID et accepte un `name`
et un `link` ACF facultatifs. Le nom sert de texte alternatif explicite ;
sinon, le titre du lien puis le texte alternatif du média sont utilisés. Le lien entoure l'image uniquement
lorsqu'il possède une URL valide. Une ligne sans image est ignorée ; sans logo
valide, la strate ne rend rien. `options` applique les options communes.

La strate utilise les composants `header` et `picture`.
Le manifeste préremplit l'introduction, mais laisse `logos` vide : aucun faux
partenaire ni visuel sans rapport n'est ajouté au site.
