# 3wa-Blog

Symfony project (week 1) for 3WA students in FSD promo

INSTALLATION

1) Open the console and choose a directory to install the project

2) Download the project with the command :
   git clone https://github.com/CamileGhastine/3wa-sf1-blog

3) Go in the directory's projet with the command :
   cd 3wa-sf1-blog

4) Install composer and its dependency with the command :
   composer install

5) Copy and past the file .env, rename it as .env.local and configure :
    - your own configuration to connect you to your database
    - your own mailer configuration

6) create de database and load fixtures with the command :
   - php bin/console doctrine:database:create
   - php bin/console doctrine:migration:migrate
   - php bin/console doctrine:fixtures:load -n

You can now enjoy the app !!!

You can connect as admin user with connection information below :
- username : admin
- password : password

You can connect as user with connection information below :
- username : camile
- password : password

VERSION
Symfony : 6.2
PHP version : 8.1.10

LINKS
- Github : https://github.com/CamileGhastine/3wa-sf1-blog