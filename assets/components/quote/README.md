# Composant `quote`

Génère une citation éditoriale avec une image, un auteur et une source
facultatifs.

## Utilisation

```php
component('quote', [
    'quote' => '<p>Une citation éditoriale.</p>',
    'image' => $image_id,
    'author' => 'Prénom Nom',
    'source' => 'Publication',
    'url' => '/publication/',
    'classes' => 'featured',
]);
```

Seule cette API est supportée. Les arguments
positionnels ne sont pas acceptés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `quote` | `scalar\|Stringable` | `''` | Citation obligatoire |
| `image` | `ID\|URL\|Stringable` | `null` | Image facultative |
| `image_size` | `string\|array{int,int}` | `'full'` | Taille WordPress de l’image |
| `author` | `scalar\|Stringable` | `''` | Nom de l’auteur |
| `source` | `scalar\|Stringable` | `''` | Titre de la publication ou de l’œuvre |
| `url` | `scalar\|Stringable` | `''` | URL facultative de la source |
| `classes` | `string\|array` | `''` | Classes supplémentaires du `<figure>` |
| `attributes` | `array` | `[]` | Attributs supplémentaires du `<figure>` |

`quote` est obligatoire. Une valeur vide ou complexe ne produit aucun HTML.
Son HTML passe par `wp_kses_post()` afin de conserver le balisage éditorial
autorisé.

`author` et `source` sont toujours échappés. L’URL est filtrée avec `esc_url()`
et n’est utilisée que si une source est présente. Une URL invalide laisse la
source en texte simple.

## Structure HTML

```html
<figure class="quote">
    <img class="quote-image" src="..." alt="...">
    <blockquote class="quote-content"><p>Une citation.</p></blockquote>
    <figcaption class="quote-caption">
        <span class="quote-author">Prénom Nom</span>
        <cite class="quote-source">
            <a href="/publication/">Publication</a>
        </cite>
    </figcaption>
</figure>
```

L’image est rendue avec `component('image')` et conserve son texte alternatif
WordPress. Le `<figcaption>` est omis si l’auteur et la source sont absents. Le
lien est omis si l’URL est absente ou invalide. `class` est réservé dans
`attributes`, car la classe du conteneur est construite par le composant.
