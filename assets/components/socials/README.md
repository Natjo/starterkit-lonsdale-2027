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

## Icônes incomplètes

Le composant vérifie automatiquement les symboles présents dans
`assets/img/icons.svg`. Lorsque le symbole du réseau existe, il appelle
`component('icon', [...])` avec le même nom. Le sprite n’est lu qu’une fois par
requête.

Si l’icône n’a pas encore été ajoutée au sprite, le nom du réseau reste visible
à la place. Une icône manquante ne crée donc ni lien vide ni SVG cassé.

## Dépendances

- `component('icon', [...])` ;
- `component_classes()` et `component_attributes()` ;
- `assets/components/socials/socials.scss`.
