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