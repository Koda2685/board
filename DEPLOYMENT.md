# VPS Deployment Guide

This project is prepared for deployment to a Linux VPS or similar hosted environment.

## 1. Server requirements

- Ubuntu 22.04+ or Debian 12+
- PHP 8.2+
- Composer
- Nginx or Apache
- MySQL 8+ or MariaDB 10.6+
- Node.js + npm (for frontend build assets)
- SSL certificate via Let's Encrypt or a managed certificate

## 2. Prepare the server

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-bcmath php8.2-curl php8.2-gd php8.2-zip unzip composer git
```

Create the database:

```bash
sudo mysql -u root -p
CREATE DATABASE dunited;
CREATE USER 'dunited'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON dunited.* TO 'dunited'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Upload the app

Copy the project to the server, then install dependencies:

```bash
cd /var/www/dunited
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 4. Configure environment

Copy the example environment and update the values:

```bash
cp .env.example .env
php artisan key:generate
```

Set the following in `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://governance.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dunited
DB_USERNAME=dunited
DB_PASSWORD=change-me

FILESYSTEM_DISK=private
MAIL_MAILER=smtp
```

## 5. Set storage permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data /var/www/dunited
```

## 6. Run database setup

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Create the first admin user if needed:

```bash
php artisan tinker
User::create([
  'name' => 'Admin User',
  'email' => 'admin@example.com',
  'password' => bcrypt('change-me'),
  'is_admin' => true,
  'email_verified_at' => now(),
]);
```

## 7. Configure Nginx

Add a site block for the app, with the public directory as the document root:

```nginx
server {
    listen 80;
    server_name governance.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name governance.example.com;

    ssl_certificate /etc/letsencrypt/live/governance.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/governance.example.com/privkey.pem;

    root /var/www/dunited/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable the site and restart nginx/php-fpm:

```bash
sudo ln -s /etc/nginx/sites-available/dunited /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

## 8. HTTPS and domain setup

Use Let's Encrypt:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d governance.example.com
```

## 9. Production hardening

- Set `APP_DEBUG=false`
- Use HTTPS only
- Configure a real mail provider
- Use object storage for governance documents if the app will host many PDFs
- Keep private files outside the public web path
- Restrict admin access and lock down routes

## 10. Post-deployment checks

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan migrate --force
```

Then confirm:

- the login page is available
- user registration works
- dashboard loads correctly
- uploaded documents open in-browser from the report links
- admin-only routes remain protected
