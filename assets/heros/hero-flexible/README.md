# Hero `flexible`

Affiche un titre `h1`, un texte d’introduction et une image responsive
pour le modèle Flexible.

```php
hero('flexible');
// Ou avec un payload explicite :
hero('flexible', [
    'title' => 'Titre',
    'text' => '<p>Introduction.</p>',
    'images' => ['desktop' => 123, 'mobile' => 456],
]);
```

Le groupe ACF `hero-flexible` contient `title` (textarea) et `text` (WYSIWYG).
Il s’affiche sur le modèle `template-flexible.php`. Les valeurs par défaut sont
les contenus Lorem ipsum du catalogue.

Sans payload explicite, si le groupe flexible n’a pas encore été enregistré, `hero()` reprend
l’ancien groupe `hero-page` pour conserver les pages déjà contribuées.
Un groupe flexible enregistré, même vide, reste prioritaire. Un payload explicite,
y compris `[]`, désactive cette récupération automatique.

## Images

Le groupe `images` contient deux champs ACF image facultatifs, `desktop` et
`mobile`, retournant des IDs WordPress. Le composant `picture` choisit la
variante mobile sous `768px` et utilise la seule image disponible si nécessaire.
Sans image valide, aucun `<picture>` n’est rendu. L’image est placée après le
contenu, avec la classe `hero-media`, et préchargée en priorité haute.

Dépendances : composants `title`, `text` et `picture`, CSS `hero-flexible.scss`
en critical.
