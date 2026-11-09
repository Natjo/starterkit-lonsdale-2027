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

Les posts absents, de statut non public ou protégés par mot de passe ne sont jamais
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

## Champs ACF dédiés aux cards

Les groupes spécifiques **Card - news**, **Card - team**, **Card - project** et **Card - expertises** regroupent leur
contribution sous une clé ACF `card`. Les champs éditoriaux de la publication
restent indépendants et ne partagent plus des clés racine `text` ou `image`.
`theme_post_card_item()` normalise ces données pour les templates : les clés
de rendu `title`, `text`, `image`, `images` restent disponibles dans `$args`.
Un champ supplémentaire propre à la card se lit dans `$args['card']['nom']`.
Les mappings métier acceptent aussi des chemins imbriqués, comme `card.function`.

**Card - testimonial** fournit `card.quote`, `card.image`, `card.author` et
`card.source` sur le CPT Testimonial. **Card - keynumber** est un groupe sans
localisation, réutilisable par clonage dans les répéteurs. Les CPT internes
(`public => false`) peuvent fournir des cards à partir de posts publiés ;
seuls leurs singles et archives restent inaccessibles au public.

**Card - page** expose `card.title` et `card.text` sur les pages WordPress,
pour leurs cards dans les recherches et remontées, indépendamment du hero.

**Card - slide** regroupe titre, texte, image et lien sous `card`. Sans
localisation ni CPT associé, il est réutilisable par clonage dans les répéteurs.

## Contenu de démonstration

Les champs texte/WYSIWYG des groupes **Card - …** disposent de valeurs ACF
`default_value` (Lorem ipsum, exemple de fonction, citation ou chiffre clé).
Les manifestes des cards déclarent également `default` sur leurs champs de
contribution : Forge convertit ces valeurs en réglages `default_value`. Les
images utilisent les références portables `__forgeStarterMediaPath` vers les
placeholders du starter ; aucun ID de média local n’est inscrit dans les JSON.
Les liens de démonstration pointent vers `/`.

Ces valeurs servent à initialiser une nouvelle contribution. Elles ne remplacent
pas les valeurs déjà enregistrées, y compris un champ volontairement vidé.
Les images et les liens n’utilisent pas un défaut natif ACF : leur préremplissage
portable est déclaré dans les manifestes pour Forge. Après synchronisation du
starter, les nouvelles contributions peuvent reprendre ces défauts. Aucun
contenu existant ni aucune base de données n’est modifié par ces déclarations.
