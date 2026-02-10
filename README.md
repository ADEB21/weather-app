# Weather App

## Description du projet

Application météo moderne développée avec Symfony 8.0 et Vue.js 3. Cette application permet aux utilisateurs de :

- Consulter les prévisions météorologiques en temps réel
- Rechercher la météo par localisation géographique
- Enregistrer des villes favorites
- Visualiser l'historique des recherches
- Afficher des données météo détaillées (température, humidité, vent, etc.)

L'application utilise l'API Open-Meteo pour récupérer les données météorologiques et intègre un service de géocodage pour la recherche de localisation.

## Technologies utilisées

- **Backend** : Symfony 8.0 (PHP 8.4+)
- **Frontend** : Vue.js 3 avec Webpack Encore
- **Base de données** : SQLite (configurable pour MySQL/PostgreSQL)
- **API météo** : Open-Meteo
- **ORM** : Doctrine

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- PHP 8.4 ou supérieur
- Composer
- Node.js (version spécifiée dans `.nvmrc`)
- npm ou yarn

## Étapes d'installation

### 1. Cloner le projet

```bash
git clone <url-du-repository>
cd weather-app
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
npm install
```

### 4. Créer la base de données

```bash
php bin/console doctrine:migrations:migrate
```

### 5. Compiler les assets

Pour le développement :

```bash
npm run dev
```

Ou pour surveiller les changements :

```bash
npm run watch
```

### 6. Démarrer le serveur de développement

```bash
symfony server:start
```

Ou avec PHP :

```bash
php -S localhost:8000 -t public/
```

L'application sera accessible à l'adresse : `http://localhost:8000`

## Commandes utiles

### Tests

Exécuter les tests unitaires :

```bash
php bin/phpunit
```

### Cache

Vider le cache :

```bash
php bin/console cache:clear
```

### Base de données

Créer une nouvelle migration :

```bash
php bin/console make:migration
```

Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

## Structure du projet

```
weather-app/
├── assets/          # Fichiers JavaScript et CSS
│   ├── vue/        # Composants Vue.js
│   └── styles/     # Feuilles de style
├── config/         # Configuration Symfony
├── migrations/     # Migrations de base de données
├── public/         # Point d'entrée web
├── src/
│   ├── Controller/ # Contrôleurs
│   ├── Entity/     # Entités Doctrine
│   ├── Repository/ # Repositories
│   └── Service/    # Services métier
├── templates/      # Templates Twig
└── tests/          # Tests unitaires et fonctionnels
```
