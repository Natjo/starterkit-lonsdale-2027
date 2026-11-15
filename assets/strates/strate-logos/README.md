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
et un `link` ACF facultatifs. Le nom est affiché sous le logo lorsqu’il est
renseigné et sert de texte alternatif explicite. Sans nom, le titre du lien
puis le texte alternatif du média sont utilisés. Le lien entoure l'image uniquement
lorsqu'il possède une URL valide. Une ligne sans image est ignorée ; sans logo
valide, la strate ne rend rien. `options` applique les options communes.

La strate utilise les composants `header` et `picture`.
Le manifeste préremplit l’introduction et les douze logos SVG de
`assets/img/placeholders/logos/`, également utilisés dans le Design System.
Ces marques servent uniquement à la démonstration et ne représentent pas des
partenaires réels du site. Les liens restent vides.
