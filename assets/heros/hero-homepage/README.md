# Hero Homepage

## Utilisation

Le premier appel charge automatiquement le champ ACF `hero-homepage`. Le second
utilise directement les données fournies.

```php
hero('homepage');
hero('homepage', ['title' => 'Test']);
```


## Data Contract
```php
[
    'suptitle' => 'Surtitre',
    'title' => 'Titre de la page',
    'text' => '<p>Introduction éditoriale.</p>',
    'images' => [
        'desktop' => 123,
        'mobile' => 124,
    ],
]
```

Le manifeste fournit deux images de démonstration depuis
`assets/img/placeholders/`. Forge les affiche comme valeurs initiales puis les
importe dans la médiathèque lors de la création du site. Les identifiants ACF
enregistrés sont donc propres à chaque installation WordPress.

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Non |           Oui |
| Titre     | `title`    | `textarea`      |         Non |           Oui |
| Texte     | `text`     | `wysiwyg`       |         Non |           Oui |
| Desktop   | `images.desktop` | `image`  |         Non |           Oui |
| Mobile    | `images.mobile`  | `image`  |         Non |           Oui |



##  Rules


## Dependencies
- `component('eyebrow', [...])`
- `component('title', [...])`
- `component('text', [...])`
