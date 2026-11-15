# Design System

Ce dossier contient le socle partagé du Design System, ses exemples et toutes
ses commandes. Il utilise les vrais composants, strates, heroes et assets du
starter, sans créer de page WordPress.

Les images de démonstration sont centralisées dans `assets/img/placeholders/`
du starter. Le Design System utilise leurs URLs compilées dans les assets du
thème ; aucune image d’exemple n’est stockée dans ce dossier.

Chaque card présente trois exemples de contenus de longueurs différentes dans
une grille dédiée, sans les wrappers de prévisualisation des composants.
Un curseur dans le titre ajuste chaque grille de 1 à 3 colonnes. Le manifeste
de la card peut définir `"designSystem": { "columns": 2 }` pour sa valeur initiale
(3 si absente ou invalide). Relancer `make docs` après modification.
Le nombre d’exemples visibles suit le nombre de colonnes sélectionné.
Les exemples restent fictifs et ne créent aucun contenu WordPress.

Pour définir les marges initiales d’un exemple de strate, ajouter à son manifeste :

```json
"designSystem": {
  "options": {
    "margin": { "top": "sm", "bottom": "0" }
  }
}
```

Valeurs : `top` accepte `""` (aucune), `sm`, `md`, `lg` ; `bottom` accepte
`"0"` (aucune), `sm`, `md`, `lg`. Chaque marge vaut `md` par défaut si elle
est absente ou invalide dans le manifeste. Relancer `make docs` pour actualiser les métadonnées.
Le rendu et les contrôles Options partagent ces valeurs ; Réinitialiser les
restaure. Ces réglages ne modifient pas les marges du site ni les défauts ACF.

Le fond s’étend jusqu’au début de `.st-components`, hors du header, avec un
réseau de particules sur Canvas, sans bibliothèque (65 points
maximum, 30 images/s). L’animation se suspend hors écran et quand l’onglet est
masqué ; elle reste fixe avec `prefers-reduced-motion`.

## Commandes

Depuis la racine du starter :

```sh
cd design-system
make help
```

| Commande depuis ce dossier | Action |
| --- | --- |
| `make docs` | Installer les dépendances et générer la documentation |
| `make watch` | Régénérer la documentation lors des changements de README ou manifestes |
| `make export URL=https://votre-starter.code` | Générer l’export dans `dist/`, sans le publier |
| `make publish` | Générer le DS et le publier dans le dépôt dédié sur `main` |
| `make test` | Tester les routes, les certificats locaux et la publication Git |

On peut aussi utiliser `make -C design-system publish` depuis la racine.
Les anciennes commandes racine (`make design-system`,
`make design-system-watch`, `make design-system-export`,
`make design-system-publish`) délèguent à ce Makefile.

## Développer le starter

Lancer Docker et le watcher CSS/JS habituels, puis `make watch` dans ce dossier.
Ouvrir `https://votre-starter.code/design-system/` : PHP au refresh, CSS/JS après
compilation par le watcher du starter. `make watch` surveille la documentation,
il ne démarre ni Docker ni le compilateur des assets du thème.

La route est virtuelle. Le thème charge `bootstrap.php` seulement avec
`ENV_LOCAL === true`. Docker monte ce dossier en lecture seule sous
`web/.forge/design-system`. Il n’y a aucun plugin à activer dans le BO.

## Publication dans le dépôt du starter

Modifier `config.json` dans ce dossier :

```json
{
  "repository": "git@github.com:Natjo/starterkit-lonsdale-2027.git",
  "branch": "design-sytem"
}
```

`make publish` utilise ces valeurs. L’URL peut être HTTPS ou SSH ; les accès Git
existants sont utilisés, sans stocker de mot de passe dans ce fichier. Le préfixe
GitHub Pages est déduit automatiquement du nom du dépôt. Pour un dépôt non vide,
une branche absente est créée avec un historique vide, sans copier les sources.

## Publier

```sh
# Depuis design-system/ :
make publish
# Ou pour préciser le site source :
make publish URL=https://votre-starter.code
```

Destination définie par `repository` et `branch` dans `config.json`.
Le domaine est automatiquement lu dans les `.env` du starter si URL est absent.
Le WordPress local doit être démarré et les assets compilés. Pour une publication
autonome, utiliser les assets de production du starter.

Le certificat `.docker/certs/{domaine}.crt` est reconnu automatiquement. HTTPS
reste vérifié, y compris le nom de domaine. Une CA supplémentaire peut être
indiquée avec `SSL_CERT_FILE`.

La publication utilise un checkout temporaire et les accès Git existants. Elle
conserve l’historique, le README distant et les réglages GitHub. Elle remplace
uniquement les rubriques et assets générés. Aucun commit si rien ne change,
aucun push forcé et aucun changement à l’index ou à l’historique du starter.
Une erreur interrompt la publication. Les tests utilisent un dépôt temporaire,
jamais GitHub.

GitHub Pages : **Settings → Pages → Deploy from a branch → design-sytem → /(root)**.
La publication ajoute `.nojekyll` et adapte les chemins au préfixe du dépôt,
avec des URLs comme `/starterkit-lonsdale-2027/styles/` ou `/starterkit-lonsdale-2027/components/`.

## Fichiers générés

- `docs/` : documentation HTML et métadonnées des sources.
- `dist/` : export HTML/CSS/JS/images prêt à publier.
- `node_modules/` : dépendances des commandes.

Ces dossiers sont ignorés par Git. Ne pas modifier `docs/` ou `dist/` à la main.
L’export ne contient ni la base ni les uploads privés. Les contrôles Common
nécessitant un rendu PHP sont figés dans l’export ; les interactions côté
navigateur restent disponibles.

## Forge

Le bouton **DS Starter** ouvre directement le site publié sur GitHub Pages.
Il ne clone pas le dépôt et ne démarre aucun conteneur.

Pour le **DS du site**, Forge installe le même socle dans le dossier local
`web/.forge/design-system`, ignoré par Git et protégé contre l’accès HTTP direct.
Il utilise le WordPress existant : aucune page ni contenu de démonstration n’est
créé en base. Un ticket de 60 secondes à usage unique ouvre une session HttpOnly
d’une heure. Sans session, la route répond 404 ; elle est désactivée en production.
Fermer la popup n’arrête pas le site.

Le dossier source `design-system/` est exclu des sites générés. Les ressources
embarquées dans Forge servent d’instantané de compatibilité ; les sources de ce
dossier sont prioritaires lorsqu’elles sont disponibles dans le starter synchronisé.

Les sources restent sur la branche de développement du starter. La branche
`design-sytem` (orthographe volontaire) contient uniquement le DS généré.

Dans Heroes et Cards, la version du manifeste est intégrée au titre de section et alignée
à droite. Dans Strates, elle termine la toolbar, après les boutons Options/README,
et s’aligne tout à droite.
