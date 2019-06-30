# Version en ligne : 

- Rendez-vous sur [Photo Share](1)


# Installation en local

## 1) Installation de Composer 

- Assurer vous d'avoir installé [Composer](2)

- Cloner le repository

## 2) Installation des dépendances

- On se place à la racine du projet

`composer install`

- Créer un fichier `.env.local` puis copier cette ligne : 

`DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name`
changer les valeurs `db_user`, `db_password` et `db_name` 
selon votre configuration et nommer la nouvelle BD.


- On crée la base :
`bin/console doctrine:database:create`

- On crée les tables et les colonnes : 
`bin/console doctrine:schema:update --force`

- On crée les fixtures (les fausses données) :
`bin/console doctrine:fixtures:load`

## 3) Tester le projet

- Lancer le serveur interne de Symfony : 
`bin/console server:run`

- Aller à l'url indiqué : 
`http://localhost:8000/` si le port est libre

- Pour vous connecter ou ajouter un ami, vous pouvez utiliser l'utilisateur `solene@gmail.com`
 (password : `solene`).


[1]: https://photo-share.webrush.fr
[2]: https://getcomposer.org/download/
