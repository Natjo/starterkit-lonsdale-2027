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

## Contribution WordPress

Le groupe ACF **Card - team** s’affiche sur les fiches du CPT Team dans le BO.

Tous les champs sont imbriqués dans le groupe ACF `card`.

| Champ | Chemin ACF | Type |
| --- | --- | --- |
| Picture | `card.image` | Image, retour ID |
| Function | `card.function` | Texte |

Le préparateur commun lit l’image directement et le mapping
`['text' => 'card.function']` fournit la fonction au composant texte.
Le titre de la publication reste le titre de la card. Les payloads directs
continuent d’utiliser `title`, `text` et `image`, sans imbrication imposée.

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
