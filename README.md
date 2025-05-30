## Domaine Du Soleil

---

### Is needed :
- [Symfony CLI](https://symfony.com/download)
- [PHP 8.2](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Git](https://git-scm.com/downloads)

---

## ⚠️ Important – Google Authentication Requires HTTPS

To use **Google authentication**, your local or deployed environment must support **HTTPS**.  
You can use Symfony’s built-in certificate authority to enable HTTPS locally:

```bash 
symfony server:ca:install
```

### To install the SSL certificate

1. Download the [cacert.pem](https://curl.se/ca/cacert.pem) file
2. Move this file to a secure folder and copy its absolute path.
3. Open your `php.ini` file in admin mode, uncomment the lines for `curl.cainfo` and `openssl.cafile` (remove the `;` at the beginning), and set their values to the full path of [cacert.pem](https://curl.se/ca/cacert.pem)

To find the location of the active php.ini file, use the following command:
```bash
php --ini
```

---

### How to install it ?
1. **Clone the project**
   ```bash 
   git clone https://github.com/samuelstaf07/DomaineDuSoleil.git
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure the .env for your DB**

4. **Create database**
   ```bash
   php bin/console d:d:c
   ```

5. **Make Migration**
   ```bash
   php bin/console make:migration
   ```

6. **Do the migration on the database**
   ```bash
   php bin/console d:m:m
   ```
7. **Start the server**
   ```bash
   symfony server:start
   ```