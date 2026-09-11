# Composant `image`

Génère une balise `<img>` à partir d'un ID WordPress ou d'une URL. Il utilise
les variantes WebP existantes et leurs dimensions mises en cache. Une liste de
formats WordPress peut alimenter automatiquement `srcset` et l'attribut HTML
`sizes`, sans produire de balise `<picture>`.

Le composant fonctionne uniquement avec `component('image', [...])` et un
payload associatif.

## Utilisation

```php
component('image', [
    'image' => $image_id,
    'size' => 'large',
    'alt' => 'Description de l’image',
    'classes' => 'cover',
]);
```

## Tailles responsives

```php
component('image', [
    'image' => $image_id,
    'sizes' => ['718_478', 'medium'],
]);
```

Le premier format disponible devient le `src` principal et définit les
dimensions intrinsèques. Tous les formats disponibles sont triés par largeur
réelle dans `srcset`. À partir de la largeur du format principal, le composant
génère automatiquement un attribut tel que
`sizes="(max-width: 718px) 100vw, 718px"`.

Chaque candidat utilise son WebP lorsqu'il existe, sinon son JPG/PNG. Les
formats inexistants et les largeurs dupliquées sont ignorés. Il est recommandé
d'utiliser des formats de même ratio, car `srcset` choisit une résolution et
ne gère pas l'art direction.

Une URL de la médiathèque WordPress est automatiquement reliée à son ID afin de
retrouver les formats demandés :

```php
component('image', [
    'image' => $image_url,
    'sizes' => ['718_478', 'medium'],
]);
```

Une URL externe reste acceptée, mais elle est rendue comme une source unique :
WordPress ne possède pas les métadonnées nécessaires pour en déduire d'autres
formats. La résolution d'une même URL vers son ID est mémorisée pendant toute
la requête PHP afin d'éviter les recherches SQL répétées.

La clé source est uniquement `image`. Les anciens alias `src`, `url` et les
arguments positionnels ne sont plus supportés.

## Champs ACF

Les champs ACF image retournent l'ID du média. Leur nom n'a aucune incidence :
la valeur est explicitement placée dans `image`.

```php
component('image', [
    'image' => $args['image'] ?? null,
    'size' => 'large',
]);

component('image', [
    'image' => $args['photo'] ?? null,
    'size' => 'large',
]);

component('image', [
    'image' => $args['visu'] ?? null,
    'size' => 'large',
]);
```

WordPress retourne la déclinaison demandée, ses dimensions et le texte
alternatif de la médiathèque. Un champ vide ne produit aucun HTML.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `image` | `ID\|URL\|Stringable` | — | Source de l'image |
| `size` | `string\|array{int,int}` | `full` | Taille WordPress |
| `sizes` | `list<string>` | `[]` | Formats WordPress du `srcset`, le premier étant la source principale |
| `alt` | `scalar\|Stringable` | alt WordPress | Texte alternatif |
| `width` | `int` | dimension détectée | Largeur explicite |
| `height` | `int` | dimension détectée | Hauteur explicite |
| `lazy` | `bool` | `true` | Active `loading="lazy"` |
| `critical` | `bool` | `false` | Rend l'image prioritaire |
| `preload` | `bool` | `false` | Précharge et rend l'image prioritaire |
| `decoding` | `async\|sync\|auto` | `async` | Stratégie de décodage |
| `fetchpriority` | `low\|auto\|high` | — | Priorité réseau |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

Une valeur `alt => ''` explicite déclare une image décorative. Sans clé
`alt`, le texte alternatif WordPress est utilisé. Pour une URL distante,
`width` et `height` permettent d'éviter un décalage de mise en page.

## Chargement

| Configuration | `loading` | `fetchpriority` | Preload |
| --- | --- | --- | --- |
| défaut | `lazy` | absent | non |
| `lazy => false` | absent | absent | non |
| `critical => true` | absent | `high` | non |
| `preload => true` | absent | `high` | oui |

`fetchpriority="high"` est accepté uniquement avec `critical` ou `preload`.
Les chaînes `"true"` et `"false"` sont normalisées. Une image critique n'est
jamais chargée en lazy.

## Sécurité

- une source vide, introuvable ou refusée par `esc_url()` ne produit rien ;
- `class`, `src`, `srcset`, `sizes`, `alt`, `width`, `height`, `loading`, `decoding` et
  `fetchpriority` sont réservés ;
- les handlers `on*`, `srcdoc` et les valeurs complexes sont refusés.

## Dépendances

- `lsd_get_thumb()` ;
- `lsd_image_webp_src()` ;
- `lsd_image_file_dimensions()` ;
- `theme_enqueue_image_preload()` ;
- `component_classes()` ;
- `component_attributes()`.
