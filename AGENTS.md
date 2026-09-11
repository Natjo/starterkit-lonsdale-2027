# Instructions pour les agents IA

Ce dépôt est un starter WordPress développé avec PHP, ACF, SCSS et JavaScript,
compilé par Rspack et exécuté avec Docker. Pour créer un site à partir du
starter, compléter puis suivre le cahier des charges `SITE.md`.

Avant toute modification, lire ce fichier puis la documentation du système et
de l'élément concernés. Les README locaux sont la source de vérité des contrats
publics.

## Organisation du projet

```text
assets/                                      Sources SCSS/JS et README des modules
web/wp-content/themes/starterkit-lonsdale-2027/
├── inc/                                     Helpers et logique du thème
├── template-parts/
│   ├── components/                          Composants réutilisables
│   ├── cards/                               Cards alimentées par un post ou un payload
│   ├── common/                              Éléments structurels globaux
│   ├── heros/                               Heroes de page
│   └── strates/                             Sections éditoriales, souvent issues d'ACF
├── assets/css/                              CSS compilé
├── assets/js/                               JavaScript compilé
└── tests/                                   Contrats PHP et navigateur
```

Modifier les sources dans `assets/`. Ne pas modifier manuellement les fichiers
compilés dans les dossiers `assets/css/` et `assets/js/` du thème.

## Documentation à lire

| Travail concerné | Documentation |
| --- | --- |
| Création ou adaptation d'un site | [`SITE.md`](SITE.md), puis les documentations techniques concernées |
| Composant | [`assets/components/README.md`](assets/components/README.md), puis le README local |
| Strate | [`assets/strates/README.md`](assets/strates/README.md), puis le README local s'il existe |
| Hero | [`assets/heros/README.md`](assets/heros/README.md), puis le README local |
| Élément global | [`assets/common/README.md`](assets/common/README.md), puis le README local |
| Card | [`assets/cards/README.md`](assets/cards/README.md), puis le README local |
| Helpers front | [`inc/front/README-front.md`](web/wp-content/themes/starterkit-lonsdale-2027/inc/front/README-front.md) |
| Configuration SEO | [`inc/config/README.md`](web/wp-content/themes/starterkit-lonsdale-2027/inc/config/README.md) |

Mettre à jour le README local lorsqu'un contrat public, une option, une valeur
par défaut ou une limite change.

## API de rendu

Utiliser uniquement les fonctions procédurales actuelles :

```php
component('title', ['title' => 'Titre']);
common('header-nav');
card('news', $post_id);
strate('text', $args);
hero('homepage', $args);
```

- Ne pas réintroduire la classe `component` ni les appels `component::...`.
- Ne pas appeler directement `get_template_part()` lorsqu'un helper de rendu
  existe.
- Passer un tableau associatif aux composants ; les tableaux positionnels ne
  font pas partie de leur API.
- Utiliser le renderer correspondant au type de template. Un composant ne doit
  pas rendre une card, une strate, un common ou un hero à sa place.

Les composants reçoivent leur payload dans `$params`. Ils doivent le normaliser
avant usage :

```php
/** @var array<int|string, mixed>|null $params */
$params = is_array($params ?? null) ? $params : [];
if (array_is_list($params)) return;
```

Les cards, strates et heroes récupèrent leurs arguments avec :

```php
$args = get_template_args();
```

Les éléments `common` constituent l’exception : ils sont appelés uniquement
avec leur nom (`common('footer')`) et récupèrent eux-mêmes leurs données
globales depuis WordPress (menus, options du site ou options ACF).

## PHP et HTML

- Utiliser `component_classes()` pour composer les classes des composants.
- Utiliser `component_attributes()` pour les attributs libres et réserver les
  attributs générés directement par le template.
- Échapper à la sortie selon le contexte : `esc_html()`, `esc_attr()`,
  `esc_url()` ou `wp_kses_post()`.
- N'autoriser `wp_kses_post()` que pour un contenu éditorial prévu pour recevoir
  du HTML.
- Normaliser les chaînes, tableaux et booléens avant le rendu.
- Un composant générique ne doit pas lire implicitement un champ ACF.
- Ne pas mettre de fonction de traduction dans un composant. Les libellés sont
  fournis par l'appelant.
- Tout nom transmis à `component('icon', [...])` doit correspondre à un
  `<symbol id="...">` présent dans `assets/img/icons.svg`. Les composants
  considèrent ce contrat comme acquis et ne doivent pas lire ou analyser le
  sprite au runtime pour vérifier la présence d'une icône.
- Les champs ACF image du projet retournent généralement un identifiant. Garder
  aussi les formats explicitement prévus par le README du composant concerné.
- Ne pas produire de balisage vide lorsque le contrat du composant prévoit de ne
  rien afficher.

## Assets et hydratation

Co-localiser les fichiers avec le même nom :

```text
template-parts/components/example.php
assets/components/example/example.scss
assets/components/example/example.js
assets/components/example/README.md
```

Le même principe s'applique à `cards`, `common`, `heros` et `strates`.

Le SCSS d'un module est enregistré par son helper de rendu. Ne pas l'importer
également dans `assets/app.scss` sauf s'il doit réellement faire partie du CSS
global.

Pour un module JavaScript hydraté :

```html
<div data-module="components/example" data-context="@visible true"></div>
```

```js
export default function initExample(root) {
    // Initialisation.
    return () => {
        // Suppression des listeners, observers, timers et RAF.
    };
}
```

- Le module doit être limité à son élément `root`.
- Le retour doit nettoyer toute ressource persistante.
- Si le module travaille continuellement, fournir `pause` et `resume` en plus de
  `destroy` afin que l'hydrateur puisse le suspendre hors écran.
- Si une racine initialise plusieurs modules, retourner leurs lifecycles dans
  un tableau. L'hydrateur ignore les valeurs absentes et les méthodes non
  fournies, puis détruit les modules dans l'ordre inverse.
- Ne pas cumuler un import direct dans `assets/app.js` et `data-module` pour le
  même module.
- Respecter `prefers-reduced-motion` pour les animations non indispensables.

## Strates et grille

- Une strate assemble des composants et des cards ; éviter d'y recopier leur
  logique interne.
- Les strates livrées avec le starter forment un catalogue de départ. Pour un
  site client, chacune peut être conservée, adaptée, renommée ou écartée selon
  le brief validé.
- Ne pas supprimer ou renommer une strate simplement parce qu'elle n'apparaît
  pas encore dans ACF ou dans une page. Attendre la validation du catalogue du
  site et une demande explicite de nettoyage.
- Lorsqu'une strate est renommée, mettre à jour ensemble son layout ACF, son
  dossier et template PHP, ses assets, son `data-module`, ses appels et sa
  documentation.
- Une strate confirmée comme inutilisée peut être supprimée en fin de mise en
  place du site, avec ses fichiers PHP, SCSS, JS et sa déclaration ACF, après
  vérification de toutes ses références.
- Le nom ACF doit correspondre au suffixe de son dossier. Exemple :
  `text_image` utilise `strate-text_image`.
- La grille expose les rails `full`, `fluid`, `ctr` et `ctr-sm`. Lire le README
  des strates avant de modifier leur largeur ou leur `subgrid`.
- Ajouter `min-width: 0` aux conteneurs de grille qui hébergent un slider, un
  marquee ou un média susceptible de déborder.

## Méthode de modification

- Préserver les changements existants : le worktree peut être volontairement
  modifié. Ne pas annuler les fichiers sans rapport avec la tâche.
- Chercher les appels et les contrats existants avant de changer une API.
- Préférer une modification ciblée à une réécriture globale.
- Ne pas ajouter une option ou une abstraction sans besoin concret.
- Ne pas modifier le HTML métier d'une card lorsque la demande porte seulement
  sur sa mécanique de rendu.
- Conserver les tests pendant la phase de développement du starter.

## Vérifications

Adapter les vérifications à la modification effectuée.

Syntaxe d'un fichier PHP depuis Docker :

```bash
docker compose -f .docker/docker-compose.yml exec -T wordpress \
  php -l /var/www/html/wp-content/themes/starterkit-lonsdale-2027/chemin/fichier.php
```

Contrats PHP des composants :

```bash
docker compose -f .docker/docker-compose.yml exec -T wordpress \
  php /var/www/html/wp-content/themes/starterkit-lonsdale-2027/tests/components-contract.php
```

Build de production des assets :

```bash
docker compose -f .docker/docker-compose.yml exec -T nodejs pnpm run prod
```

Pour une modification documentaire uniquement, `git diff --check` suffit. Pour
un comportement interactif, compléter les contrôles statiques par un test dans
un vrai navigateur.
