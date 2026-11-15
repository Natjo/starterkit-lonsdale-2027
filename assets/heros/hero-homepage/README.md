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
    'link' => ['url' => '/contact/', 'title' => 'Nous contacter', 'target' => ''],
    'images' => [
        'desktop' => 123,
        'mobile' => 124,
    ],
]
```

Le manifeste fournit un surtitre, un titre et un texte Lorem ipsum, ainsi que
deux images de démonstration depuis
`assets/img/placeholders/`. Forge les affiche comme valeurs initiales puis les
importe dans la médiathèque lors de la création du site. Les identifiants ACF
enregistrés sont donc propres à chaque installation WordPress.
Ces valeurs servent au préremplissage dans Forge, pas de fallback PHP : un champ
volontairement vidé reste vide. Pour un site déjà créé, la synchronisation du
catalogue ne remplace pas les contributions existantes.

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Non |           Oui |
| Titre     | `title`    | `textarea`      |         Non |           Oui |
| Texte     | `text`     | `wysiwyg`       |         Non |           Oui |
| Lien      | `link` | `link` (tableau ACF) | Non | Oui |
| Desktop   | `images.desktop` | `image`  |         Non |           Oui |
| Mobile    | `images.mobile`  | `image`  |         Non |           Oui |



##  Rules


## Dependencies
- `component('eyebrow', [...])`
- `component('title', [...])`
- `component('text', [...])`

Le lien facultatif est rendu après le texte avec `component('link')`.
Sans URL ou libellé valide, aucun lien n’est affiché. Le manifeste fournit
« En savoir plus » pour les nouvelles contributions Forge, sans remplacer un
lien existant ou volontairement supprimé.

- Dépendance : `component('link', [...])`.
