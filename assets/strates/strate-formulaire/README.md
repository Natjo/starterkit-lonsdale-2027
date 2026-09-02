# Strate `formulaire`

Strate statique réservée au styleguide. Elle permet de visualiser tous les
types actuellement supportés par le composant `field` :

- `text`, `email`, `tel`, `url`, `password`, `number` et `date` ;
- `select` et `textarea` ;
- `radios`, `checkboxes` et `checkbox` ;
- boutons `submit` et `reset`.

```php
strate('formulaire');
```

La strate n’accepte aucun contenu ni aucune option fonctionnelle. Tous les
champs et libellés sont volontairement écrits directement dans son template.
Elle sert uniquement à contrôler visuellement le rendu, les états et la
validation des champs.
