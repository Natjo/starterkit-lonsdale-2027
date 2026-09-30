# Strate `text-image`

Affiche un bloc éditorial composé d’un titre, d’un texte riche et d’une image.
À partir du breakpoint `md`, le contenu et le média occupent chacun une moitié
du rail `ctr`.

Lire d’abord le [README général des strates](../README.md).

## Appel

Depuis le flexible content ACF, le dispatcher appelle automatiquement :

```php
strate('text-image', $strate);
```

Appel direct :

```php
strate('text-image', [
    'title' => 'Titre de la section',
    'text' => '<p>Contenu éditorial.</p>',
    'image' => 123,
    'reverse' => true,
    'center' => true,
]);
```

Le nom du layout ACF est `text-image`. Le préfixe `strate-` appartient
uniquement au dossier et au fichier PHP.

## Options

| Option | Effet à partir du breakpoint `md` |
| --- | --- |
| `reverse` | Place l’image à gauche et le texte à droite |
| `center` | Centre verticalement le texte et l’image |

## Contrat

| Clé | Type | Valeur ACF vide | Rôle |
| --- | --- | --- | --- |
| `title` | `mixed` | Titre Lorem ipsum | Transmis à `component('title')` |
| `text` | `mixed` | Deux paragraphes Lorem ipsum | Transmis à `component('text')` |
| `image` | `ID\|URL\|null` | Placeholder du manifeste | Transmis à `component('picture')` |
| `reverse` | `bool` | `false` | Inverse les colonnes à partir du breakpoint `md` |
| `center` | `bool` | `false` | Centre verticalement les deux colonnes |
| `options` | `array` | `[]` | Options communes traitées par `options()` |

## Champs ACF

Layout flexible content :

```text
acf_fc_layout = text-image
```

| Libellé | Identifiant | Type ACF | Format |
| --- | --- | --- | --- |
| Titre | `title` | WYSIWYG léger | HTML éditorial filtré par le composant |
| Texte | `text` | WYSIWYG | HTML éditorial filtré par le composant |
| Image | `image` | Image | ID de média |
| Inverser l’ordre | `reverse` | Vrai/Faux | Booléen, défaut `false` |
| Centrer verticalement | `center` | Vrai/Faux | Booléen, défaut `false` |
| Options | `options` | Groupe cloné | Options communes des strates |

Le titre et le texte de démonstration viennent des `default_value` ACF. Le
placeholder dédié mesure `1460 × 973 px`, soit deux fois la largeur du format
WordPress `730_auto`. Il est déclaré dans le manifeste : Forge l’importe dans la
médiathèque et remplace sa référence par l’identifiant propre au site créé.

## Mise en page

Avant le breakpoint `md`, le texte et l’image sont empilés.

À partir du breakpoint `md`, ils occupent chacun une moitié du rail `ctr` :

- `reverse` inverse leur position ;
- `center` les centre verticalement.

## Assets et dépendances

```text
template-parts/strates/strate-text-image.php
assets/strates/strate-text-image/strate-text-image.scss
```

Dépendances :

- `options()` ;
- `component('title', [...])` ;
- `component('text', [...])` ;
- `component('picture', [...])`.
