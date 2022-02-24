# YourQuest

Service web permettant la mise en place, et la participation à des jeux de piste (à l’échelle d’un bâtiment, d’une ville, etc.).
L’utilisation d’un smartphone par les joueurs sera nécessaire pour scanner des QR codes permettant de valider leur avancée au fur et à mesure des checkpoints, et afficher des énigmes qu’il faudra résoudre pour obtenir la localisation du checkpoint suivant.

## Après avoir cloné ce dépôt localement

  1. Effectuer un `composer install`
  2. Créer une base de données MariaDB avec un utilisateur associé, ayant tous les droits
  3. Créer un fichier `.env.local` et y ajouter les informations de connection à la base de données (à personnaliser suivant votre de basse de données)
    `DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.3.25"` 
  4. Ouvrir le terminal et faire un `bin/console d:m:m` pour envoyer les fichiers de migration en base de données
  5. Lancer le serveur pour voir le site en tapant cette commande dans le terminal:  `php -S 0.0.0.0:8080 -t public`
  6. Installer le composant en ligne de commande dans le terminal avec `composer require --dev orm-fixtures`pour charger les fixtures
  7. Lancer les fixtures avec la commande `bin/console doctrine:fixtures:load` dans le terminal
