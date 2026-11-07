# Common `prefooter`

Affiche le titre, le texte éditorial et les réseaux sociaux du préfooter global.
La version avec formulaire est la variante
[`prefooter-newsletter`](../prefooter-newsletter/README.md).

## Appel

```php
common('prefooter');
```

Appelé dans `front-page.php` dans le starter, sans payload.

## Données WordPress

Lit le groupe ACF `prefooter` dans les options globales (`Options > Prefooter`).

| Champ | Rôle |
| --- | --- |
| `title` | Titre |
| `text` | Texte éditorial riche |
| `socials` | Répéteur des réseaux et de leurs URL |

Les champs newsletter ne sont pas lus par ce common. Aucun HTML n’est produit
si le titre, le texte et la liste des réseaux sont vides.

## Dépendances

- Composants `title`, `text` et `socials` ;
- `assets/common/prefooter/prefooter.scss`, intégré à `app.css`.
