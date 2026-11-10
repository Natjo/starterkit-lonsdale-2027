# Composant `text`

Génère un bloc de contenu dans une balise `<div>`. Le HTML autorisé par
WordPress est conservé et la classe CSS `text` est toujours ajoutée.

Le composant fonctionne uniquement avec `component('text', [...])` et un
payload associatif.

## Utilisation

```php
component('text', [
    'text' => '<p>Texte de présentation.</p>',
    'classes' => 'intro',
    'attributes' => [
        'id' => 'introduction',
        'aria-label' => 'Introduction',
    ],
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `text` | `scalar\|Stringable` | `''` | Contenu éditorial |
| `classes` | `string\|array` | `''` | Classes ajoutées à `text` |
| `attributes` | `array` | `[]` | Attributs HTML supplémentaires |

Une valeur `text` vide, complexe ou composée uniquement d'espaces ne produit
aucun HTML. Les anciennes clés `intro`, `legend`, `content` et `description`
ne sont plus interprétées : elles doivent être mappées explicitement à l'appel.

```php
component('text', [
    'text' => $args['intro'] ?? '',
    'classes' => 'intro',
]);
```

## Règles de rendu

- la balise générée est toujours un `<div>` ;
- la classe structurelle `text` est toujours présente ;
- le contenu passe par `wp_kses_post()` ;
- les classes sont nettoyées et dédupliquées ;
- les attributs sont validés et échappés ;
- `class` ne peut pas être remplacé via `attributes`.

## Exemple de sortie

```php
component('text', [
    'text' => '<p>Une information <strong>importante</strong>.</p>',
    'classes' => 'intro',
]);
```

Produit :

```html
<div class="text intro">
    <p>Une information <strong>importante</strong>.</p>
</div>
```

## Tableaux RTE

Avec la classe `rte`, les tableaux occupent toute la largeur disponible, avec
des bordures fusionnées de la couleur du texte, des cellules espacées et
alignées en haut à gauche, et des en-têtes en gras.

## Dépendances

- `component_classes()` ;
- `component_attributes()` ;
- `wp_kses_post()`.

## Dépendance de l’éditeur WordPress

`.rte` et le format `.intro` sont définis dans `assets/components/text/text.scss`.
Le CSS du WYSIWYG importe ce fichier et est compilé pour chaque site : le
manifeste active donc `text` en permanence. Il n’existe pas de SCSS distinct
pour Intro. Forge conserve le composant `text` lors du nettoyage.

Le composant `intro` a été retiré. Pour une introduction éditoriale, utiliser
`component('text', ['text' => $text, 'classes' => 'intro'])`. La strate `intro`
et le format Intro du WYSIWYG restent disponibles.
