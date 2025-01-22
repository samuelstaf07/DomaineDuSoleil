## Domaine Du Soleil

---

### Is needed :
- [Symfony CLI](https://symfony.com/download)
- [PHP 8.2](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Git](https://git-scm.com/downloads)

---

### How to install it ?
1. **Clone the project**
    ```bash 
   git clone https://github.com/samuelstaf07/DomaineDuSoleil.git

2. **Install dependencies**
   ```bash 
   composer install

3. **Configure the .env for your DB**

4. **Create database**
    ```bash
   php bin/console d:d:c

5. **Make Migration**
    ```bash
    php bin/console make:migration

6. **Do the migration on the database**
    ```bash
    php bin/console d:m:m
7. **Start the server**
    ```bash
    symfony server:start