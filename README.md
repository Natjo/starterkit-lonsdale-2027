# Starterkit v2

## Stack Docker

WordPress (php8.3-fpm) / Node / MariaDB (local) / Composer / Nginx / SonarQube / LibreCrawl

Solution dockerisée avec HTTPS local. La base de données tourne en local (MariaDB/MySQL sur la machine hôte) par défaut.

---

## Prérequis

- Docker & Docker Compose
- MariaDB ou MySQL installé localement
- Node.js (pour les scripts hors container)
- `make`

---

## Installation rapide

```bash
make init
```

Le script interactif effectue automatiquement :

1. Demande le nom du projet (par défaut : nom du dossier)
2. Demande l'URL du dépôt Git distant
3. Crée le fichier `.docker/.env` depuis `.env-sample`
4. Renomme le dossier thème selon le nom du projet
5. Configure le fichier `/etc/hosts`
6. Génère et approuve les certificats SSL
7. Crée la base de données locale
8. Met à jour le `Theme Name` dans `style.css`
9. Met à jour le `table_prefix` dans `wp-config.php`
10. Génère les clés d'authentification WordPress
11. Installe WordPress et active le thème
12. Lance les containers et ouvre le navigateur

---

## Commandes Make

| Commande | Description |
|---|---|
| `make init` | Initialisation complète du projet (à lancer une seule fois) |
| `make start` | Démarrage des containers (avec `--build` en option) |
| `make stop` | Arrêt et suppression des containers |
| `make composer [cmd]` | Exécution d'une commande Composer dans un container dédié |
| `make sonar` | Lancement du container SonarQube |
| `make crawl` | Lancement de LibreCrawl (audit SEO/accessibilité) et ouverture dans le navigateur |
| `make prod` | Génération des assets en mode production via le container Node |

### Exemples

```bash
# Démarrer le projet
make start

# Installer un package composer
make composer require monolog/monolog

# Générer les assets de production
make prod
```

`make prod` peut être utilisé pendant que le watcher Docker tourne : il ne
supprime pas les chunks déjà présents. Pour une génération finale entièrement
propre, arrêter d’abord le service Node puis lancer la commande dédiée :

```bash
docker compose -f .docker/docker-compose.yml stop nodejs
docker compose -f .docker/docker-compose.yml run --rm --no-deps nodejs pnpm run prod:clean
```

Pour reprendre le développement ensuite, redémarrer `nodejs` ; le watcher
réémettra alors les assets de développement.

---

## Dépendances front-end conseillées

Ces packages ne sont pas installés par défaut mais sont recommandés selon les besoins du projet :

```bash
# Transitions de pages
pnpm add @barba/core

# Animations
pnpm add gsap

# Smooth scroll
pnpm add lenis

# Carrousel
pnpm add swiper

# Lazy loading images
pnpm add vanilla-lazyload
```

---

## Configuration

### Fichier .env

Le fichier `.docker/.env` est créé automatiquement par `make init`. Variables principales :

```
PROJECT_NAME    # Nom du projet (sans espaces ni accents)
DOMAIN          # Domaine local (ex: mon-projet.code)
WP_THEME_NAME   # Nom du thème WordPress
DB_HOST         # host.docker.internal (BDD locale)
DB_NAME         # Nom de la base de données
DB_USER         # Utilisateur BDD
DB_PASSWORD     # Mot de passe BDD
NODE_PORT       # Port du serveur de dev Node (défaut: 3000)
SONAR_KEY       # Clé SonarQube (optionnel)
SONAR_TOKEN     # Token SonarQube (optionnel)
```

### docker-compose

Un fichier `.docker/docker-compose-sample.yml` est fourni. Il peut être dupliqué en `.docker/docker-compose.yml` pour affiner la stack (platform, services optionnels...).

---

## Scripts utilitaires

Les scripts suivants se trouvent dans `.docker/scripts/` :

| Script | Description |
|---|---|
| `bdd-import.sh` | Import d'un fichier .sql dans la base |
| `bdd-export.sh` | Export de la base vers un fichier .sql |
| `node-assets-prod.sh` | Génération des assets en mode prod |
| `node-docker-enter.sh` | Entrer dans le container Node |
| `hosts-file-setup.sh` | Ajout du domaine dans `/etc/hosts` |
| `cert-create.sh` | Génération des certificats SSL |
| `cert-trust.sh` | Approbation des certificats (Chrome/Safari) |
| `sonar-scan.sh` | Lancement d'un scan SonarQube |

---

## Containers

| Container | Rôle |
|---|---|
| **wordpress** (php8.3-fpm) | WordPress avec WP-CLI |
| **apache** | Reverse proxy HTTPS (ports 80/443) |
| **nodejs** | Compilation assets (dev & prod) |
| **composer** | Gestion des dépendances PHP |
| **sonarqube** (profil) | Analyse qualité du code |
| **librecrawl** (profil) | Audit SEO/accessibilité |

### Accéder aux containers

```bash
# WordPress (WP-CLI disponible)
docker exec -it ${PROJECT_NAME}-wp bash
wp --info

# Node
docker exec -it ${PROJECT_NAME}-node bash
```

---

## Commandes Docker utiles

```bash
# Voir tous les conteneurs
docker ps -a

# Arrêter tous les conteneurs
docker container stop $(docker container ls -aq)

# Supprimer tous les conteneurs ET images
docker rm -v $(docker ps -a -q)
docker rmi -f $(docker images -q)

# Rebuild avec force
docker compose up -d --force-recreate --build

# Logs d'un conteneur
docker logs [container] -f
```

---

## Déploiement CI/CD

### 1. Activer la CI/CD

Renommer `.gitlab-ci-sample.yml` en `.gitlab-ci.yml`

### 2. Ticket Bearstech

```
Bonjour

Pouvez-vous :
- Créer un environnement de préprod "[projet].preprod.lonsdale.io" sur "lonsdale-preprod.ovh.bearstech.com".
- Créer un environnement de prod "nom de domaine" sur "lonsdale.ovh.bearstech.com".
- Mettre en place une protection htaccess sur la preprod
- Activer HTTPS via lets encrypt.
- Ajouter ces Variables d'environnement pour que nous puissions y accéder depuis le code php (pouvez-vous remplacer les "XXXX" par la valeur correspondante que vous connaitrez lors de la création de l'environnement)

° MYSQL_DATABASE=XXXXX
° MYSQL_USER=XXXXX
° MYSQL_PASSWORD=XXXXX
° MYSQL_HOST=127.0.0.1

- Ajouter cette clé pour l'accès au serveur "lonsdale-preprod.ovh.bearstech.com" ?
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQC06uGrpswAZrIpRQfiAxOrGiuLZguefwzyljyL4VPlBo+YPNl7vcJB3NeQ2DllWH7khHCkYHk0+RoXL2mRembUlfMwT5+CZyDRuD26QAPwVFBRVqOuZW+WAP0XK9+prMJobQqtV7B0JcrzRLTiLZ3lcCtPqnuJEWEJ3mTTMCmD53Htnkh7w1t0arEaXedBDkQ4LXqbNCjz8PU1lxDf5V/bzoDGGTNBHW3bEqNEUF8KqRqDbh8vDE4JwVWalR5ypZdH0dfcAjG6YLD+fi+MnqMOjMjZSWmj6PNVe537zPXwGiqPCwHl7eVrHA0+dUVQ1cRAJfM7HIOzIqR6j/JxLkc7 deploy@lonsdale.fr

Merci
```

### 3. Variables GitLab CI/CD

Télécharger la clé privée depuis https://projects.lonsdale.fr/#/files/7047136

Dans **Settings > CI/CD > Variables** :

| Variable | Valeur |
|---|---|
| `PP_DOCROOT` | root |
| `PP_SSH_SERVER` | lonsdale-preprod.ovh.bearstech.com |
| `PP_SSH_USER` | [user du projet] |
| `SSH_KEY` | Contenu de la clé privée téléchargée |
| `SONAR_TOKEN` | [Valeur depuis le notebook](https://projects.lonsdale.fr/#/notebooks/1036788?catid=0) |

Dans le fichier `.gitlab-ci.yml` : décommenter les lignes 7 à 22.

Créer une branche `preprod` et la protéger dans **Settings > Repository > Protected Branches** (maintainers dans les selects).

### 4. Installation de la préprod

Se connecter au serveur Bearstech :

```bash
ssh user@lonsdale-preprod.ovh.bearstech.com
```

Générer une clé SSH et l'ajouter dans **Settings > Repository > Deploy Keys** :

```bash
ssh-keygen
cat ~/.ssh/id_rsa.pub
```

Vider le dossier root et cloner le projet :

```bash
cd root
rm -rf web
git clone [url-du-repo] .
git checkout preprod
```

### 5. Base de données Bearstech

Pour récupérer les infos de connexion à la base sur la préprod :

```bash
cd [le-repo-du-projet]/
cat .my.cnf
```

Informations de connexion pour un client SQL (TablePlus, Sequel Pro...) :

| Paramètre | Valeur |
|---|---|
| Host | 127.0.0.1 |
| User | [user du projet] |
| Password | (depuis `.my.cnf`) |
| SSH Host | lonsdale-preprod.ovh.bearstech.com |
| SSH User | [user du projet] |
| SSH Key | `~/.ssh/id_rsa` (clé locale) |
