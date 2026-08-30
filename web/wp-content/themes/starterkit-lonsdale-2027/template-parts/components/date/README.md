# Composant `date`

Affiche une date avec une valeur machine fiable. Le composant fonctionne
uniquement avec `component('date', [...])` et un payload associatif.

Il utilise `<time>` lorsqu’un `datetime` valide est disponible, sinon un
`<span>` afin de ne pas exposer une fausse sémantique.

## Utilisation

```php
component('date', [
    'date' => '20 juillet 2026',
    'datetime' => '2026-07-20',
    'classes' => 'date-news',
    'attributes' => ['data-tracking' => 'publication-date'],
]);
```

Les arguments positionnels, les listes directes et
l’ancien alias `text` ne sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `date` | `scalar\|Stringable` | `''` | Libellé visible obligatoire |
| `datetime` | `scalar\|Stringable\|DateTimeInterface` | auto | Valeur machine explicite |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Si `datetime` est absent ou invalide, le composant tente de reconnaître une
date machine dans le libellé visible.

## Formats reconnus

La normalisation est stricte et n’utilise pas `strtotime()` :

- `Y-m-d`, par exemple `2026-07-20` ;
- `Ymd`, par exemple `20260720`, format brut courant d’ACF ;
- `d/m/Y`, par exemple `20/07/2026` ;
- `DateTimeInterface`, converti dans le fuseau WordPress.

Toutes les valeurs valides sont rendues au format `Y-m-d`. Les dates
impossibles comme `2026-02-30` sont rejetées.

```php
component('date', [
    'date' => '20 juillet 2026',
    'datetime' => '20260720',
]);
```

Produit :

```html
<time class="date" datetime="2026-07-20">20 juillet 2026</time>
```

Sans valeur machine fiable :

```php
component('date', [
    'date' => '20 juillet 2026',
]);
```

Produit un `<span class="date">`.

## Rendu et sécurité

- Une valeur `date` vide ou invalide ne produit aucun HTML.
- `class` et `datetime` sont protégés.
- Le contenu visible autorise uniquement `span`, `strong`, `em`, `abbr`,
  `small`, `sup`, `sub` et `br`.
- Les scripts, liens et balises de bloc sont supprimés.
- Les classes sont nettoyées et dédupliquées.
- Les attributs libres sûrs restent disponibles sur `<time>` ou `<span>`.
- Aucun JavaScript n’est nécessaire.

## Dépendances

- `component_classes()` pour les classes ;
- `component_attributes()` pour les attributs ;
- `wp_kses()` pour le contenu inline ;
- `wp_timezone()` pour le fuseau WordPress.
