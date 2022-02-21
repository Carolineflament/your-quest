# Cahier des charges

## Présentation

TODO

## Besoins et objectifs

**Permettre à un organisateur de mettre en place un jeu de piste, en mettant à sa disposition une application lui permettant de :**

- Générer un jeu de piste.
- Générer ses checkpoints.
- Rédiger les énigmes posées aux joueurs à chaque étape.
- Générer les QR codes pour chaque checkpoint.
- Générer un document PDF multipages contenant les QR codes, prêt à imprimer pour pouvoir afficher un QR code unique à chaque checkpoint du jeu.
- Afficher une page présentant l'avancée des joueurs étapes par étapes, au cours du jeu.

**Permettre à un joueur de participer à un jeu de piste en lui permettant de :**

- Scanner un QR avec son smartphone à chaque étape du jeu.
- Répondre à des énigmes pour obtenir la localisation du checkpoint suivant.
- Afficher une page présentant l'avancée des joueurs étapes par étapes, au cours du jeu.


## Les fonctionnalités

### Le MVP

#### Backoffice

- Authentification et ACL pour les roles "Admin" et "Organisateur".
- Rôle "Admin" :
  - Possibilité pour le rôle "Admin" de désactiver un organisateur ou un jeu.
- Rôle "Organisateur" :
  - Création de compte pour les organisateurs.
  - Gestion de la réinitialisation d'un mot de passe oublié (avec envoi d'un lien par email).
  - Créer un ou plusieurs jeux de pistes.
  - Créer des checkpoints.
  - Créer les messages affichés sur le front de l'application à chaque checkpoint validé, indiquant aux joueurs la localisation du checkpoint suivant.
  - Génération des QR codes nécessaires à la "validation" des checkpoints pour chaque jeu.
  - Génération d'un document PDF prêt à imprimer.
  - Afficher l'avancée des joueurs en cours de partie.

#### Front

- Création de compte pour les joueurs.
- Gestion de la réinitialisation d'un mot de passe oublié (avec envoi d'un lien par email).
- Afficher l'avancée des joueurs en cours de partie.


### Les évolutions

**V2 :**

- Mise en place d'une fonctionnalité de lecture des QR codes depuis le front de l'application (library JS).
- Créer des énigmes par checkpoint :
  - Backoffice : l'organisateur peut rédiger des énigmes et les choix multiples de leurs réponses (QCM).
  - Front : le joueur doit répondre correctement aux énigmes pour obtenir la localisation du checkpoint suivant.

**V3 :**

- Possibilité de participer à un jeu n'importe quand (et non plus uniquement lors d'un événement commun pour tous les joueurs), et d'être intégré à un classement de résultats.
- Afficher un tableau des meilleurs scores (Temps les plus courts pour rallier le dernier checkpoint).
- Liste des prochains jeux de piste organisés sur l'application (événements).
- Carte de géolocalisation des jeux de piste en cours d'exploitation.


### Technologies

Back avec le framework Symfony
Rendu des vues du backoffice avec TWIG
Style CSS avec Bootstrap
Base de donnée MariaDB

Front avec Twig (+ library JS pour lecteur de QR code en Version 2 du projet)

