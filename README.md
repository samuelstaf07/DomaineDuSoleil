## Domaine Du Soleil
### Is needed :
    - Symfony CLI: "https://symfony.com/download"
    - PHP 8.2 "https://www.php.net/downloads.php"
### How to install it ?
    - git clone https://github.com/samuelstaf07/DomaineDuSoleil.git
    - composer install
    - Configure the .env for your DB
    - php bin/console d:d:c
    - php bin/console make:migration
    - php bin/console d:m:m
    - symfony server:start