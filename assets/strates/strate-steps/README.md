# Strate `steps`

Affiche une suite ordonnée d'étapes. La numérotation est générée par CSS et
reste correcte lorsque des lignes incomplètes sont ignorées.

```php
strate('steps', [
    'title' => 'Notre méthode',
    'steps' => [
        ['title' => 'Écouter', 'text' => '<p>Votre besoin.</p>'],
        ['title' => 'Créer', 'link' => ['url' => '/contact/', 'title' => 'Nous contacter']],
    ],
]);
```

Le layout ACF propose un titre et un texte d'introduction facultatifs, puis
un repeater `steps`. Chaque ligne demande un `title` et accepte un `text`
WYSIWYG et un `link` ACF facultatifs. Une ligne sans titre est ignorée ; sans
étape valide, la strate ne rend rien. `options` applique les options communes.

Le manifeste fournit trois étapes d'exemple pour Forge. La strate utilise les
composants `header`, `title`, `text` et `link`.
