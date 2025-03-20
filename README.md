# Maker Bundle

## Introduction
Le Maker Bundle est un outil conçu pour simplifier la création de fonctionnalités dans vos projets Symfony. Il fournit des commandes personnalisées pour générer rapidement du code et des configurations nécessaires.

## Installation

1. Assurez-vous que le bundle est inclus dans votre fichier `composer.json`.
2. Exécutez la commande suivante pour installer les dépendances :
   ```bash
   composer install
   ```
3. Vérifiez que le bundle est bien enregistré dans le fichier `config/bundles.php` :
   ```php
   return [
       // ...existing bundles...
       Akyos\MakerBundle\AkyosMakerBundle::class => ['all' => true],
   ];
   ```

## Utilisation

### Commandes Disponibles

#### 1. Génération de CRUD
Utilisez la commande suivante pour générer un CRUD :
```bash
php bin/console make:akyos-crud
```
Cette commande vous guidera à travers les étapes nécessaires pour créer un CRUD complet.

### Personnalisation
Les fichiers générés peuvent être modifiés selon vos besoins. Vous pouvez trouver les fichiers générés dans les répertoires appropriés, tels que `src/Controller`, `src/Entity`, et `templates/`.

## Support
Pour toute question ou problème, veuillez consulter la documentation officielle ou contacter l'équipe de développement.

