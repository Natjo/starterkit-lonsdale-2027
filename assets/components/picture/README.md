# Composant `picture`

Génère une balise `<picture>` à partir d'IDs de médias WordPress ou d'URLs. Il
gère une variante desktop/mobile, les WebP existants, les dimensions, le lazy
loading et le preload des images critiques.

Le composant fonctionne uniquement avec `component('picture', [...])` et un
payload associatif.

## Image simple

Les champs ACF image retournent l'ID du média :

```php
component('picture', [
    'image' => $args['image'] ?? null,
    'size' => 'large',
    'fit' => 'fluid',
    'classes' => 'cover',
]);
```

## Variantes desktop et mobile

```php
component('picture', [
    'images' => [
        'desktop' => $args['image_desktop'] ?? null,
        'mobile' => $args['image_mobile'] ?? null,
    ],
    'desktop_size' => 'large',
    'mobile_size' => 'medium_large',
    'breakpoint' => 768,
]);
```

`desktop` et `mobile` sont acceptés uniquement dans `images`. Les anciennes
formes positionnelles et les variantes placées à la racine ne sont plus
supportées.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `image` | `ID\|URL` | — | Source simple recommandée |
| `size` | `string\|array{int,int}` | `full` | Taille commune |
| `images` | `ID\|URL\|array{desktop?,mobile?}` | — | Compatibilité simple ou variantes responsive |
| `desktop_size` | `string\|array{int,int}` | valeur de `size` | Taille WordPress desktop |
| `mobile_size` | `string\|array{int,int}` | valeur de `size` | Taille WordPress mobile |
| `fit` | `'fluid'\|'cover'` | `'fluid'` | Comportement dimensionnel de l’image |
| `alt` | `scalar\|Stringable` | alt WordPress | Texte alternatif du fallback |
| `classes` | `string\|array` | `''` | Classes du `<picture>` |
| `attributes` | `array` | `[]` | Attributs du `<picture>` |
| `lazy` | `bool` | `true` | Active `loading="lazy"` |
| `critical` | `bool` | `false` | Force une priorité haute |
| `preload` | `bool` | `false` | Précharge et rend l'image critique |
| `placeholder` | `bool` | `false` | Rend un `<picture>` vide sans image |
| `breakpoint` | `int` | `768` | Séparation desktop/mobile en pixels |

Les chaînes `"true"` et `"false"` sont normalisées. Une valeur `alt => ''`
explicite permet de déclarer l'image décorative.

Si `image` et `images` sont fournis ensemble, `images` est prioritaire. De la
même manière, `desktop_size` et `mobile_size` remplacent `size` pour leur
variante respective.

## Ajustement dans le conteneur

Le mode `fluid`, utilisé par défaut, conserve les proportions naturelles :

```css
width: 100%;
height: auto;
```

Le mode `cover` remplit la hauteur disponible :

```css
width: 100%;
height: 100%;
object-fit: cover;
```

Le composant ne fixe aucun ratio. La strate, la card ou le module concerné
définit `aspect-ratio` selon son design :

```scss
.slider-image {
    aspect-ratio: 4 / 3;
}
```

## Chargement

| Configuration | `loading` | `fetchpriority` | Preload |
| --- | --- | --- | --- |
| défaut | `lazy` | absent | non |
| `lazy => false` | absent | absent | non |
| `critical => true` | absent | `high` | non |
| `preload => true` | absent | `high` | oui |

Avec deux variantes, le preload utilise les mêmes media queries que les
sources : mobile sous le breakpoint et desktop à partir du breakpoint.

Avec une seule image, qu'elle soit fournie par `image`, `images.desktop` ou
`images.mobile`, le composant rend le WebP existant dans un `<source>` sans
media query. Le `<img>` conserve l'image d'origine comme fallback. Le preload
cible le WebP lorsqu'il existe, sinon l'image d'origine.

## WebP et dimensions

- aucune conversion n'est effectuée pendant le rendu ;
- une variante WebP existante est utilisée dans un `<source>` ;
- l'original reste le fallback dans `<img>` ;
- les recherches WebP et leurs dimensions conservent les caches existants ;
- `width` et `height` sont rendus lorsqu'ils sont connus.

Le composant ne génère volontairement aucun `srcset` ou `sizes` WordPress. Le
`srcset` présent sur les balises `<source>` désigne uniquement une variante
desktop, mobile ou WebP précise.

## Placeholder

```php
component('picture', [
    'image' => null,
    'classes' => 'strate-image',
    'placeholder' => true,
]);
```

Produit :

```html
<picture class="picture fit-fluid placeholder strate-image"></picture>
```

## Sécurité

- les sources sont filtrées avec `esc_url()` ;
- les classes passent par `component_classes()` ;
- les attributs passent par `component_attributes()` ;
- `class` ne peut pas être remplacé via `attributes` ;
- une source invalide ne produit aucun média.

## Dépendances

- `lsd_get_thumb()` ;
- `lsd_image_webp_src()` ;
- `lsd_image_file_dimensions()` ;
- `starter_enqueue_preload_image()` ;
- `wp_check_filetype()`.
