# Composant `socials`

Affiche une liste de liens vers les profils sociaux du site. Il ne s’agit pas
du composant `shares`, qui partage la page courante.

## Utilisation

```php
component('socials', [
    'items' => [
        ['network' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example/'],
        ['network' => 'instagram', 'url' => 'https://www.instagram.com/example/'],
    ],
    'aria_label' => 'Retrouvez-nous',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `items` | `array` | `[]` | Liste des réseaux et de leurs URL |
| `aria_label` | `scalar\|Stringable` | `'Réseaux sociaux'` | Nom accessible de la navigation |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs libres du conteneur |

Chaque ligne de `items` accepte uniquement :

```php
[
    'network' => 'linkedin',
    'url' => 'https://www.linkedin.com/company/example/',
]
```

Réseaux reconnus : `facebook`, `instagram`, `linkedin`, `x`, `youtube`,
`tiktok`, `pinterest` et `threads`.

Les réseaux inconnus, URL non HTTP(S) et doublons sont ignorés. Tous les liens
s’ouvrent dans un nouvel onglet avec `noopener noreferrer`.

## Icônes

Le composant appelle `component('icon', [...])` avec le nom du réseau. Chaque
réseau reconnu par le catalogue doit donc posséder un symbole portant le même
identifiant dans `assets/img/icons.svg`.

## Dépendances

- `component('icon', [...])` ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/socials/socials.scss`.
