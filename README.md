# SAE 3.1 - KickScore

## Petit manuel de lancement

Après clonage de l'application, il faut faire `composer install` pour installer l'ensemble des bundles de Symfony nécessaires à l'application, et `npm install` pour installer les modules Node (Webpack Encore, Tailwind).

Pour le `.env` manquant, il sera fourni.

Puisque l'application de base utilise Webpack Encore pour compiler le JavaScript et le CSS de Tailwind, il faut premièrement lancer la commande :
```sh
user@host$~/KickScore/ npm run build
[...]
user@host$~/KickScore/ symfony serve
```

À chaque modification du CSS (dans `/assets`) ou du JavaScript, il faudra recompiler pour intégrer les éventuels changements.

Le serveur Symfony ira automatiquement récupérer les changements dans `/public` sans avoir besoin d'être relancé.



## Tests de cohérence de la base

A la racine du projet, vous trouverez le fichier `testCoherence.sql` qui contient les tests vérifiant que les données sont bien cohérentes avec les contraintes d'intégrité.

Lancez les commandes suivantes pour exécuter les tests : 
```sh
user@host$~/KickScore/ export MYSQL_PWD=XmXp2QKR
user@host$~/KickScore/ mysql -u etu_matchabrier -h info-titania etu_matchabrier < testCoherence.sql
```

## Conventions de codage, code PHP

Afin de rendre le code aéré et moins lourd visuellement, nous avons séparé les méthodes d'un saut de ligne.
Le code est exclusivemement en anglais, mis à part certains textes quin apparaîtront sur l'application.
Afin de rendre le code le plus maintenable possible, nous avons attribué un à trois commentaires pour chaque méthode.
Nous avons, dans le but d'uniformiser notre programme, utilisé la convention de nommage Camel Case pour chaque méthode et pour chaque attribut.
Nous avons également placé rigoureusement des exceptions afin d'éviter le logiciel de planter.

## Convention de codage, procédure stockée en SQL

IF, THEN, ENDIF, DECLARE, LOOP en majucule // Attributs et opérateurs logiques en minuscule 
-> Créer du contraste et améliore la lisibilité

Espaces : 
 - Entre chaque condition
 - Entre chaque boucle
 - Après le début et la fin des boucles
 - Deux en haut de chaque procédure
 - Après le début du begin des procédures
 -> Aérer le code

Code et commentaires en anglais
-> Améliorer la maintenabilité

1 à 3 commentaires par fonction / méthode
-> Améliorer la maintenabilité sans surcharger

Camel case
-> Eviter les caractères spéciaux et unifier la convention de nommage


