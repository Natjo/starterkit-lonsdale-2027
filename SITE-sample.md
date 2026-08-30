# Cahier des charges du site

> Ce fichier est le modèle de brief fonctionnel d'un site créé à partir du
> starter. Il décrit **ce que le site doit administrer**. Les règles techniques
> d'implémentation restent dans `AGENTS.md` et les README du thème.

## Mode d'emploi

Avant de lancer la création d'un site :

1. remplacer les valeurs entre crochets `[À renseigner]` ;
2. supprimer les sections qui ne concernent pas le projet ;
3. décrire chaque type de contenu, hero et strate attendu ;
4. faire valider le modèle de données avant de générer tous les groupes ACF.

Une information laissée à `[À renseigner]` est inconnue. L'agent ne doit pas
l'inventer si elle modifie l'architecture éditoriale du site.

## 1. Projet

| Information | Valeur |
| --- | --- |
| Nom du site | `[À renseigner]` |
| Slug du thème | `[À renseigner]` |
| Objectif | `[À renseigner]` |
| Public principal | `[À renseigner]` |
| Domaine prévu | `[À renseigner]` |
| Langue par défaut | `fr` |
| Autres langues | `[Aucune / À renseigner]` |
| Extension multilingue | `[Aucune / Polylang / WPML / À renseigner]` |
| Multisite | `Non` |

## 2. Sources fonctionnelles

| Source | Lien ou emplacement | Statut |
| --- | --- | --- |
| Maquettes | `[Lien Figma ou dossier]` | `[À fournir / Validé]` |
| Arborescence | `[Lien ou section ci-dessous]` | `[À fournir / Validé]` |
| Spécifications | `[Lien ou fichier]` | `[À fournir / Validé]` |
| Contenus | `[Lien ou dossier]` | `[À fournir / Validé]` |
| Redirections | `[Lien ou fichier]` | `[Non concerné / À fournir / Validé]` |

Les maquettes servent à identifier les contenus administrables et les familles
de strates. Elles ne suffisent pas à décider seules des règles métier, des
relations entre contenus ou des droits utilisateurs.

## 3. Périmètre de cette phase

Cette phase prépare uniquement le back-office WordPress et la structure PHP
nécessaire au futur front :

- types de contenus et taxonomies ;
- pages et templates WordPress nécessaires ;
- menus et pages d'options ;
- groupes de champs ACF et fichiers `acf-json` ;
- flexible content `strates` ;
- heroes administrables ;
- templates PHP minimaux des nouvelles strates ;
- contrats documentés dans les README locaux.

Sont hors périmètre, sauf demande explicite :

- intégration graphique finale ;
- responsive et animations ;
- JavaScript interactif ;
- optimisation visuelle des composants ;
- saisie de tous les contenus définitifs ;
- modification d'un composant existant uniquement pour reproduire une
  différence cosmétique de maquette.

## 4. Arborescence

| Page | Slug souhaité | Modèle | Hero | Strates autorisées | Remarques |
| --- | --- | --- | --- | --- | --- |
| Accueil | `/` | `front-page.php` | `[À renseigner]` | `[À renseigner]` | `[À renseigner]` |
| `[Nom]` | `[slug]` | `[page / template dédié]` | `[À renseigner]` | `[À renseigner]` | `[À renseigner]` |

Préciser ici les pages dont la structure doit être verrouillée et celles qui
peuvent être librement composées avec le flexible content.

## 5. Types de contenus

Les pages et articles WordPress natifs sont :

- Pages : `[Utilisées / Non utilisées / Règles à préciser]` ;
- Articles : `[Utilisés / Non utilisés / Règles à préciser]`.

### Types personnalisés

Dupliquer le tableau pour chaque Custom Post Type attendu.

#### `[Nom éditorial du contenu]`

| Propriété | Valeur |
| --- | --- |
| Clé technique | `[exemple : project]` |
| Slug public | `[À renseigner]` |
| Singulier / pluriel | `[À renseigner]` |
| Archive publique | `[Oui / Non]` |
| Page individuelle | `[Oui / Non]` |
| Recherche interne | `[Oui / Non]` |
| REST API | `[Oui / Non]` |
| Supports WordPress | `[title, editor, thumbnail, excerpt, revisions…]` |
| Taxonomies liées | `[À renseigner]` |

Champs spécifiques :

| Label administrateur | Nom ACF | Type | Obligatoire | Valeur par défaut | Règles |
| --- | --- | --- | --- | --- | --- |
| `[Label]` | `[field_name]` | `[text / image / relationship…]` | `[Oui / Non]` | `[Valeur]` | `[Instructions]` |

## 6. Taxonomies

| Nom | Clé | Contenus liés | Hiérarchique | Archive | Remarques |
| --- | --- | --- | --- | --- | --- |
| `[Nom]` | `[taxonomy_key]` | `[post types]` | `[Oui / Non]` | `[Oui / Non]` | `[À renseigner]` |

Ne pas créer une taxonomie lorsqu'une simple liste de choix locale à un champ
suffit. Utiliser une taxonomie lorsque les termes doivent être partagés,
filtrés, archivés ou administrés indépendamment.

## 7. Menus et options globales

### Menus

| Emplacement | Nom technique | Usage |
| --- | --- | --- |
| Navigation principale | `[À renseigner]` | Header |
| Pied de page | `[À renseigner]` | Footer |

### Options globales

| Groupe | Champ | Nom ACF | Type | Obligatoire | Remarques |
| --- | --- | --- | --- | --- | --- |
| Coordonnées | `[À renseigner]` | `[field_name]` | `[type]` | `[Oui / Non]` | `[À renseigner]` |
| Réseaux sociaux | `[À renseigner]` | `[field_name]` | `[type]` | `[Oui / Non]` | `[À renseigner]` |
| Footer | `[À renseigner]` | `[field_name]` | `[type]` | `[Oui / Non]` | `[À renseigner]` |

Ne placer dans les options que les données réellement globales. Une donnée
propre à une page, une strate ou un contenu doit rester attachée à celui-ci.

## 8. Conventions des champs ACF

- Sauvegarder les groupes dans `web/wp-content/themes/<slug-du-thème>/acf-json/`.
- Employer des labels administrateur explicites et des noms techniques courts,
  stables et en anglais.
- Les champs image retournent un identifiant WordPress.
- Documenter le format de retour des liens, relations, galeries et sélecteurs.
- Définir explicitement les valeurs par défaut des choix et booléens.
- Utiliser la logique conditionnelle pour masquer les champs sans effet.
- Ajouter une instruction ACF lorsque le rôle ou le format attendu n'est pas
  évident pour un contributeur.
- Ne rendre obligatoire que ce qui empêche réellement le rendu ou invalide le
  contenu métier.
- Réutiliser les groupes existants avec clone lorsque leur contrat est commun.
- Conserver des clés ACF uniques et stables. Ne pas régénérer les clés d'un
  groupe existant sans nécessité.

### Formats de retour du projet

| Type ACF | Format attendu |
| --- | --- |
| Image | ID WordPress |
| Galerie | `[IDs / À renseigner]` |
| Lien | `[Tableau ACF / URL / À renseigner]` |
| Relation | `[Objet / ID / À renseigner]` |
| Taxonomie | `[Objet / ID / À renseigner]` |

## 9. Heroes

| Nom | Pages concernées | Groupe ACF | Champs | Template |
| --- | --- | --- | --- | --- |
| `[homepage]` | `[Accueil]` | `[Hero - Homepage]` | `[À renseigner]` | `hero-homepage` |
| `[page]` | `[Pages internes]` | `[Hero - Page]` | `[À renseigner]` | `hero-page` |

Pour chaque hero, préciser :

- s'il est obligatoire ;
- où ses champs sont stockés ;
- les variantes autorisées ;
- les contenus requis ;
- les règles de fallback.

## 10. Flexible content et strates

Le constructeur de page utilise le flexible content ACF `strates`.

La correspondance doit rester exacte :

```text
acf_fc_layout = text_image
template       = template-parts/strates/strate-text_image/strate-text_image.php
appel          = strate('text_image', $strate)
```

Le nom ACF conserve donc l'underscore lorsqu'il fait partie du nom du layout.
L'appel n'ajoute pas le préfixe `strate-`.

### Options communes

Les strates configurables réutilisent le groupe ACF existant `options`. Il peut
gérer notamment l'identifiant, la largeur, les marges, le fond et les
espacements internes selon son contrat actuel.

Ne pas réintroduire les anciennes options `menu_name` et `is_lonsdale`.
Consulter le README des strates avant de modifier les options ou les rails
`full`, `fluid`, `ctr` et `ctr-sm`.

### Catalogue du site

| Strate | Existe dans le starter | Pages autorisées | Statut | Remarques |
| --- | --- | --- | --- | --- |
| `[nom_technique]` | `[Oui / Non]` | `[Toutes / Liste]` | `[À conserver / Adapter / Créer]` | `[À renseigner]` |

Les strates du starter constituent un catalogue réutilisable, pas une liste à
conserver intégralement dans chaque site. Pour chacune, choisir explicitement
un statut :

- `Conserver` : contrat et nom repris tels quels ;
- `Adapter` : base existante conservée avec un contrat ajusté ;
- `Renommer` : même besoin sous un nom métier plus pertinent ;
- `Créer` : aucune strate existante ne couvre le besoin ;
- `Supprimer` : strate confirmée comme inutile pour le site final.

Ne rien supprimer pendant l'audit initial. Les suppressions interviennent
seulement après validation complète du catalogue et vérification des références
PHP, ACF, SCSS, JavaScript, styleguide et documentation. Un renommage doit être
appliqué à toute cette chaîne, notamment à `acf_fc_layout`, au dossier
`strate-<nom>` et à l'éventuel `data-module`.

Pour chaque strate à créer ou adapter, remplir la fiche suivante.

### Strate `[nom_technique]`

**Rôle éditorial :** `[À renseigner]`

**Source dans les maquettes :** `[Lien, écran ou frame]`

**Règles :**

- `[À renseigner]`
- `[À renseigner]`

| Label administrateur | Nom ACF | Type | Obligatoire | Défaut | Format / choix / limite |
| --- | --- | --- | --- | --- | --- |
| `[Titre]` | `[title]` | `[text]` | `[Oui / Non]` | `[Valeur]` | `[À renseigner]` |

Composants ou cards à réutiliser :

```text
[component('header'), component('picture'), card('news')…]
```

Variantes nécessaires :

```text
[Aucune / À renseigner]
```

Comportement lorsque les données sont absentes :

```text
[Ne rien afficher / Masquer seulement la sous-partie / Fallback à préciser]
```

## 11. Templates PHP à préparer

Pour chaque nouvelle strate, créer au minimum :

```text
template-parts/strates/strate-<nom>/strate-<nom>.php
template-parts/strates/strate-<nom>/README.md
assets/strates/strate-<nom>/strate-<nom>.scss
```

Le SCSS peut rester minimal pendant la phase back. Ne créer le fichier JS que
si un comportement front est explicitement demandé.

Le template PHP doit :

1. récupérer les données avec `$args = get_template_args();` ;
2. utiliser `options()` si la strate expose les options communes ;
3. transmettre explicitement ses données aux composants et cards ;
4. tolérer les champs facultatifs absents ou mal formés ;
5. éviter toute récupération ACF implicite dans un composant générique ;
6. ne pas contenir de styles inline ni de logique JavaScript ;
7. respecter le contrat documenté dans son README local.

Exemple minimal :

```php
<?php
$args = get_template_args();
?>

<section <?= options('strate strate-example', $args) ?>>
    <div class="strate-content">
        <?php component('header', [
            'eyebrow' => $args['eyebrow'] ?? '',
            'title' => $args['title'] ?? '',
            'text' => $args['text'] ?? '',
        ]); ?>
    </div>
</section>
```

## 12. Accès et rôles

| Rôle | Peut administrer | Restrictions |
| --- | --- | --- |
| Administrateur | Tout le site | Aucune |
| Éditeur | `[À renseigner]` | `[À renseigner]` |
| `[Rôle personnalisé]` | `[À renseigner]` | `[À renseigner]` |

Si aucun besoin particulier n'est indiqué, conserver les rôles WordPress
natifs et ne pas créer de système de permissions supplémentaire.

## 13. Données de démonstration

| Besoin | Valeur |
| --- | --- |
| Créer les pages principales | `[Oui / Non]` |
| Ajouter des contenus fictifs | `[Oui / Non]` |
| Créer les menus | `[Oui / Non]` |
| Affecter une page d'accueil | `[Oui / Non]` |
| Importer des médias | `[Oui / Non, source]` |

Toute écriture dans la base WordPress doit être explicitement demandée. La
création des fichiers PHP et ACF JSON n'autorise pas automatiquement l'import
ou la suppression de contenus en base.

## 14. Décisions à valider avant implémentation

L'agent doit signaler les informations manquantes qui changeraient fortement le
modèle de données, notamment :

- distinction entre page, CPT, taxonomie et simple champ de choix ;
- contenu global ou propre à une page ;
- relation simple ou multiple ;
- ordre manuel, chronologique ou automatique ;
- archive et page individuelle d'un CPT ;
- règles multilingues ;
- strate libre, limitée à certaines pages ou structure verrouillée ;
- format de retour ACF incompatible avec un contrat existant.

Les détails réversibles peuvent être traités avec une hypothèse documentée.

## 15. Critères de validation

- Les CPT, taxonomies, menus et options correspondent au brief validé.
- Les groupes ACF sont versionnés dans `acf-json` et synchronisables dans
  WordPress.
- Chaque layout ACF possède un template au nom correspondant.
- Chaque strate nouvelle ou modifiée possède un README à jour.
- Les champs facultatifs absents ne provoquent ni warning ni erreur PHP.
- Les images utilisent le format de retour ID attendu par le starter.
- Les composants existants sont réutilisés sans réintroduire `component::`.
- Les anciennes options `menu_name` et `is_lonsdale` restent absentes.
- La syntaxe PHP et les contrats existants passent.
- Aucun CSS ou JavaScript compilé n'a été modifié manuellement.

## Prompt d'exécution

Une fois ce fichier complété, utiliser ce prompt :

```text
Lis AGENTS.md, SITE.md et tous les README vers lesquels ils te dirigent.

Prépare uniquement la partie back-office WordPress définie dans SITE.md :
architecture des contenus, CPT, taxonomies, menus, options globales, groupes
ACF, flexible content, heroes, templates PHP minimaux et README des strates.

Commence par auditer l'existant et compare-le au brief. Présente ensuite le
modèle de données et la liste exacte des fichiers à créer ou modifier. Signale
uniquement les informations manquantes qui changeraient matériellement
l'architecture. Après validation de ces décisions, implémente la partie
suffisamment définie.

Réutilise les helpers et composants du starter. Ne réalise pas encore le design
final, les animations ou le JavaScript front. Ne modifie pas les fichiers
compilés et ne touche pas à la base de données sans autorisation explicite.

Termine par les vérifications adaptées et un récapitulatif des éléments créés,
des hypothèses retenues et des points restant à valider.
```
