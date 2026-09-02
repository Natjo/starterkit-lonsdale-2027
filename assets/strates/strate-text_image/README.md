# Strate `text_image`

Affiche un bloc éditorial composé d’un titre, d’un texte riche et d’une image.
À partir du breakpoint `md`, le contenu et le média occupent chacun une moitié
du rail `ctr`.

Lire d’abord le [README général des strates](../README.md).

## Appel

Depuis le flexible content ACF, le dispatcher appelle automatiquement :

```php
strate('text_image', $strate);
```

Appel direct :

```php
strate('text_image', [
    'title' => 'Titre de la section',
    'text' => '<p>Contenu éditorial.</p>',
    'image' => 123,
    'reverse' => true,
    'center' => true,
]);
```

Le nom du layout ACF reste `text_image`. Le préfixe `strate-` appartient
uniquement au dossier et au fichier PHP.

## Options

| Option | Effet à partir du breakpoint `md` |
| --- | --- |
| `reverse` | Place l’image à gauche et le texte à droite |
| `center` | Centre verticalement le texte et l’image |

## Contrat

| Clé | Type | Valeur ACF vide | Rôle |
| --- | --- | --- | --- |
| `title` | `mixed` | `''` | Transmis à `component('title')` |
| `text` | `mixed` | `''` | Transmis à `component('text')` |
| `image` | `ID\|URL\|null` | `null` | Transmis à `component('picture')` |
| `reverse` | `bool` | `false` | Inverse les colonnes à partir du breakpoint `md` |
| `center` | `bool` | `false` | Centre verticalement les deux colonnes |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

## Champs ACF

Layout flexible content :

```text
acf_fc_layout = text_image
```

| Libellé | Identifiant | Type ACF | Format |
| --- | --- | --- | --- |
| Titre | `title` | WYSIWYG léger | HTML éditorial filtré par le composant |
| Texte | `text` | WYSIWYG | HTML éditorial filtré par le composant |
| Image | `image` | Image | ID de média |
| Inverser l’ordre | `reverse` | Vrai/Faux | Booléen, défaut `false` |
| Centrer verticalement | `center` | Vrai/Faux | Booléen, défaut `false` |
| Options | `options` | Groupe cloné | Options communes des strates |

## Mise en page

Avant le breakpoint `md`, le texte et l’image sont empilés.

À partir du breakpoint `md`, ils occupent chacun une moitié du rail `ctr` :

- `reverse` inverse leur position ;
- `center` les centre verticalement.

## Assets et dépendances

```text
template-parts/strates/strate-text_image.php
assets/strates/strate-text_image/strate-text_image.scss
```

Dépendances :

- `options()` ;
- `component('title', [...])` ;
- `component('text', [...])` ;
- `component('picture', [...])`.
