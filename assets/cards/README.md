# Cards

Les cards sont rendues avec `card()` et vivent dans :

```text
template-parts/cards/card-{nom}.php
```

Le préfixe `card-` est facultatif : `news` et `card-news` ciblent le même
template.

Le contrat propre à chaque card est documenté dans son dossier sous
`assets/cards/`. Le starter
contient notamment `card-news`, `card-project`, `card-expertise`, `card-team`,
`card-page`, `card-slide` et `card-keynumber`.

`card-page` sert aussi de fallback sur la page de recherche lorsqu’un nouveau
CPT ne possède pas encore de card dédiée.

## Depuis un post WordPress

```php
card('news', 123);
```

L’ID doit être un entier strictement positif. `card()` récupère les champs ACF
du post et les données normalisées de `theme_post_card_item()` (groupe `card`,
mapping métier, puis replis WordPress), ajoute `post_id`, puis transmet le payload au template avec
`get_template_args()`.

Les posts absents, non publics ou protégés par mot de passe ne sont jamais
rendus, même pour un administrateur. Une surcharge ne contourne pas ce contrôle.

## Depuis un contenu personnalisé

```php
card('news', [
    'title' => 'Titre personnalisé',
    'text' => '<p>Contenu personnalisé.</p>',
]);
```

Le contenu doit être un tableau associatif non vide. Les tableaux positionnels
et les autres scalaires sont refusés.

## Post avec surcharges

```php
card('news', [
    'post_id' => 123,
    'title' => 'Titre remplacé',
]);
```

Les champs ACF et les données normalisées sont chargés en premier. Les valeurs données explicitement dans
le tableau les remplacent. `post_id` reste toujours l’entier validé.

Dans une card :

```php
$args = get_template_args();
$post_id = $args['post_id'] ?? 0;
```

Le dispatcher ne modifie jamais le post global avec `setup_postdata()`. Il
restaure aussi le précédent `template_args` après chaque rendu, y compris en
cas d’exception.
