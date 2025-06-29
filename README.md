# Vide Grenier en Ligne

[![Build Status](https://img.shields.io/badge/Build-Passing-brightgreen)](https://github.com/hanan3889/bloc5/actions/workflows/build-and-push-docker.yml)
[![Deploy Status](https://img.shields.io/badge/Deploy-Passing-brightgreen)](https://github.com/hanan3889/bloc5/actions/workflows/deploy-to-prod-on-main-merge.yml)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Ce Readme.md est à destination des futurs repreneurs du site-web Vide Grenier en Ligne.

## Contexte du Projet

Ce projet a été conçu dans le cadre d'un apprentissage approfondi de Docker et de la conteneurisation. Il met en œuvre une architecture basée sur Docker pour faciliter le développement, le déploiement et la gestion des différents environnements.

## Environnements Docker 

Le projet est structuré autour de deux environnements Docker distincts, permettant une gestion indépendante du développement et de la production :

### 1. Environnement de Développement (DEV) 

*   **Description :** Cet environnement est conçu pour le développement local. Il utilise un "bind mount" pour que les modifications du code source soient immédiatement reflétées dans le conteneur, sans nécessiter de reconstruction d'image.
*   **Accès :** `http://videgrenier-dev:8000/`
*   **Démarrage :** `./start-dev.sh`
*   **Arrêt :** `./stop-dev.sh`
*   **Redémarrage :** `./restart-dev.sh`

### 2. Environnement de Production (PROD)

*   **Description :** Cet environnement est optimisé pour le déploiement en production. Le code est intégré directement dans l'image Docker lors de sa construction, garantissant que les changements ne sont appliqués qu'après un processus de build et de déploiement contrôlé.
*   **Accès :** `http://videgrenier-prod:9089/`
*   **Démarrage :** `./start-prod.sh`
*   **Arrêt :** `./stop-prod.sh`
*   **Redémarrage :** `./restart-prod.sh`
*   **Reconstruction de l'image :** `./rebuild-prod.sh` 

**Note sur les alias (`videgrenier-dev`, `videgrenier-prod`) :** Pour que ces adresses fonctionnent, vous devez ajouter les entrées correspondantes dans le fichier `hosts` de votre système (par exemple, `C:\Windows\System32\drivers\etc\hosts` sur Windows) :

```
127.0.0.1 videgrenier-dev
127.0.0.1 videgrenier-prod
```

## Automatisation

Le projet inclut des scripts (`.sh`) pour simplifier la gestion des conteneurs Docker. Ces scripts sont conçus pour démarrer, arrêter et redémarrer les environnements de manière robuste.

## Tests Unitaires

Le projet intègre des tests unitaires pour assurer la qualité et la fiabilité du code. Ces tests peuvent être exécutés pour valider le comportement des différentes composantes de l'application.

## Documentation API (Swagger UI)

Une interface Swagger UI est mise en place dans l'environnement de développement pour faciliter la consultation et le test des API.

### Accès à Swagger UI

*   **URL :** `http://videgrenier-dev:8000/swagger/index.html`

### Mise en place de Swagger UI

L'intégration de Swagger UI a été réalisée en utilisant une approche de fichier OpenAPI JSON statique, en raison de défis rencontrés avec la génération dynamique via les annotations PHP dans l'environnement Docker.

1.  **Téléchargement de Swagger UI :** Les fichiers de distribution de Swagger UI ont été téléchargés et placés dans le répertoire `public/swagger/`.
2.  **Configuration Apache :** Le fichier `apache/dev.conf` a été modifié pour servir les fichiers de Swagger UI depuis `public/swagger/`.
3.  **Fichier OpenAPI JSON Statique :** Un fichier `public/swagger/openapi.json` a été créé manuellement. Ce fichier contient la définition de l'API au format OpenAPI 3.0.0.
4.  **Configuration de Swagger UI :** Le fichier `public/swagger/swagger-initializer.js` a été mis à jour pour pointer vers le fichier `openapi.json`.

**Note :** Pour mettre à jour la documentation de l'API, vous devrez modifier manuellement le fichier `public/swagger/openapi.json`.

## Mise en place du projet (sans Docker - pour référence)

1. Créez un VirtualHost pointant vers le dossier /public du site web (Apache)
2. Importez la base de données MySQL (sql/import.sql)
3. Connectez le projet et la base de données via les fichiers de configuration
4. Lancez la commande `composer install` pour les dépendances

## Mise en place du projet front-end (sans Docker - pour référence)
1. Lancez la commande `npm install` pour installer node-sass
2. Lancez la commande `npm run watch` pour compiler les fichiers SCSS

## Routing

Le [Router](Core/Router.php) traduit les URLs. 

Les routes sont ajoutées via la méthode `add`. 

En plus des **controllers** et **actions**, vous pouvez spécifier un paramètre comme pour la route suivante:

```php
$router->add('product/{id:\d+}', ['controller' => 'Product', 'action' => 'show']);
```


## Vues

Les vues sont rendues grâce à **Twig**. 
Vous les retrouverez dans le dossier `App/Views`. 

```php
View::renderTemplate('Home/index.html', [
    'name'    => 'Toto',
    'colours' => ['rouge', 'bleu', 'vert']
]);
```
## Models

Les modèles sont utilisés pour récupérer ou stocker des données dans l'application. Les modèles héritent de `Core
\Model
` et utilisent [PDO](http://php.net/manual/en/book.pdo.php) pour l'accès à la base de données. 

```php
$db = static::getDB();
```
