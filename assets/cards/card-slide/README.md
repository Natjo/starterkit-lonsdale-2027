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

## Groupe ACF réutilisable

**Card - slide** regroupe `card.title` (texte), `card.text` (WYSIWYG Basic),
`card.image` (ID) et `card.link` (tableau ACF). Tous les champs sont facultatifs.
Le groupe n’a pas de règle de localisation et ne crée aucun CPT : il peut être
cloné dans un répéteur, en conservant le nom `card` sans préfixage des champs.
La card et la strate Slider Cards acceptent les lignes imbriquées sous `card`,
ainsi que les anciens payloads plats. Les champs actuels de la strate restent
inchangés ; une ligne sans titre, texte ni image est toujours ignorée.

Les nouvelles contributions disposent de valeurs de démonstration ; voir le
[contrat des valeurs par défaut](../README.md#contenu-de-démonstration).
