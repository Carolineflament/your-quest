# Documentation des commandes

## CreateActiveInstanceCommand

Cette commande lance la création d'un nouveau jeu et une nouvelle instance valide depuis 1h et pendant encore 4h.
Grâce aux arguments qu'on lui fournit, elle permet de choisir :

- Le nom du nouveau jeu
- Son nombre de checkpoints
- Le nom de la nouvelle instance
- Le nombre de joueurs à créer qui auront un round en cours ou terminé sur cette instance

La commande a 4 arguments obligatoires et se lance comme ceci :

`bin/console app:instance:now gameName numberOfCheckpoints instanceName numberOfPlayers`