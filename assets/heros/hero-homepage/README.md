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
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Non |           Oui |
| Titre     | `title`    | `textarea`      |         Non |           Oui |
| Texte     | `text`     | `wysiwyg`       |         Non |           Oui |



##  Rules


## Dependencies
- `component('eyebrow', [...])`
- `component('title', [...])`
- `component('text', [...])`
