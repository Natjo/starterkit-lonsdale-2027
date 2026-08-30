# Composant `shares`

Affiche des actions pour partager une URL par e-mail, copie, Facebook,
LinkedIn, X ou WhatsApp. Le nom du composant est `shares`, au pluriel, et il
fonctionne uniquement avec `component('shares', [...])`.

## Utilisation

```php
component('shares', [
    'list' => ['copy', 'linkedin', 'email'],
    'url' => get_permalink(),
    'title' => 'Partager cette page',
    'subject' => 'À découvrir',
    'classes' => 'shares-article',
    'attributes' => ['data-tracking' => 'share'],
]);
```

Les arguments positionnels et les listes directes ne
sont plus supportés.

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `list` | `array` | `[]` | Services à afficher |
| `url` | `scalar\|Stringable` | URL courante | URL HTTP(S) à partager |
| `title` | `scalar\|Stringable` | `''` | Titre visible du groupe |
| `subject` | `scalar\|Stringable` | `''` | Objet du partage par e-mail |
| `aria_label` | `scalar\|Stringable` | `'Partager'` | Nom accessible sans titre visible |
| `copied_label` | `scalar\|Stringable` | `'Lien copié'` | Message de copie réussie |
| `copy_error_label` | `scalar\|Stringable` | `'Impossible de copier le lien'` | Message d’échec |
| `classes` | `string\|array` | `''` | Classes supplémentaires |
| `attributes` | `array` | `[]` | Attributs supplémentaires |

Le composant ne contient aucune clé de traduction. L’appelant peut transmettre
les trois textes d’interface déjà traduits.

Sans `url`, le composant utilise le permalien du contenu singulier, puis une
URL construite avec `home_url()` et le chemin courant. Il ne fait pas confiance
à l’en-tête HTTP `Host`. Seules les URL HTTP(S) sont acceptées.

## Services

Liste simple :

```php
'list' => ['copy', 'facebook', 'linkedin', 'x', 'whatsapp', 'email'],
```

Les doublons et services inconnus sont ignorés.

Liste détaillée :

```php
'list' => [
    ['name' => 'linkedin', 'label' => 'Publier sur LinkedIn'],
    ['name' => 'copy', 'icon' => 'link'],
],
```

Liste associative :

```php
'list' => [
    'linkedin' => ['label' => 'Partager avec mon réseau'],
    'copy' => [],
],
```

Chaque définition accepte `label`, `icon` et `href`. L’ancien alias `url` de
`href` n’est plus interprété.

Une destination personnalisée doit être HTTP(S), sauf pour `email`, qui
accepte une adresse ou un lien `mailto:` :

```php
'list' => [
    'email' => ['href' => 'contact@example.com'],
],
```

## Accessibilité et comportement

- Les réseaux sociaux sont des liens avec `target="_blank"` et
  `rel="noopener noreferrer"`.
- L’e-mail est un lien `mailto:` sans nouvelle fenêtre forcée.
- La copie utilise un `<button type="button">`.
- Chaque action possède un libellé accessible indépendant de son icône.
- Le succès ou l’échec de la copie est annoncé dans une région `aria-live`.
- Le fallback de copie restaure le focus et la sélection du document.
- Le nettoyage annule les feedbacks et ignore une copie asynchrone terminée
  après destruction.

Sans JavaScript, tous les liens restent utilisables ; seule la copie dans le
presse-papiers dépend du module.

## Attributs protégés

`class`, `aria-label`, `aria-labelledby`, `data-context` et `data-module` sont
contrôlés par le composant.

## Dépendances

- `assets/components/shares/shares.js` ;
- `assets/components/shares/shares.scss` ;
- `component('icon', [...])` ;
- `component_classes()` et `component_attributes()`.
