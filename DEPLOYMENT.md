# CRM — VPS Deployment Guide (Hostinger)

Complete step-by-step guide to deploy the CRM application on a Hostinger VPS (Ubuntu 22.04/24.04).

---

## Prerequisites

| Requirement   | Minimum   | Recommended |
|---------------|-----------|-------------|
| PHP           | 8.2+      | 8.3         |
| MySQL         | 8.0+      | 8.0         |
| Composer      | 2.x       | Latest      |
| Node.js       | 18+       | 22 LTS      |
| RAM           | 2 GB      | 4 GB        |
| Storage       | 20 GB     | 40 GB+      |
| OS            | Ubuntu 22.04 | Ubuntu 24.04 |

---

## Step 1 — SSH into Hostinger VPS

```bash
ssh root@YOUR_VPS_IP
```

---

## Step 2 — Update System

```bash
apt update && apt upgrade -y
```

---

## Step 3 — Install PHP 8.3 + Extensions

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-imap php8.3-fileinfo php8.3-tokenizer php8.3-soap php8.3-redis
```

---

## Step 4 — Install Nginx

```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

---

## Step 5 — Install MySQL 8.0

```bash
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

# Secure MySQL
mysql_secure_installation
```

Create database and user:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE time_vault_crm;
CREATE USER 'timevault'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON time_vault_crm.* TO 'timevault'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 6 — Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

---

## Step 7 — Install Node.js 22 LTS

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs
```

---

## Step 8 — Install Redis (for Queue & Cache)

```bash
apt install -y redis-server
systemctl enable redis-server
systemctl start redis
```

---

## Step 9 — Install Supervisor (for Queue Worker)

```bash
apt install -y supervisor
systemctl enable supervisor
```

---

## Step 10 — Clone Project

```bash
apt install -y git
cd /var/www
git clone YOUR_REPO_URL time-vault-panel
cd /var/www/time-vault-panel
```

Or upload via SFTP if no Git repo.

---

## Step 11 — Install Dependencies

```bash
cd /var/www/time-vault-panel

composer install --no-dev --optimize-autoloader

npm install
npm run build
```

---

## Step 12 — Configure Environment

```bash
cp .env.example .env
nano .env
```

Set these values in `.env`:

```env
APP_NAME='CRM'
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=time_vault_crm
DB_USERNAME=timevault
DB_PASSWORD=YOUR_STRONG_PASSWORD

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME='CRM'

# Add Twilio, Pusher, IMAP keys as needed
```

---

## Step 13 — Generate App Key & Run Migrations

```bash
cd /var/www/time-vault-panel

php artisan key:generate
php artisan migrate --force
php artisan krayin-crm:install --skip-env-check
php artisan storage:link
```

---

## Step 14 — Set Permissions

```bash
chown -R www-data:www-data /var/www/time-vault-panel
chmod -R 755 /var/www/time-vault-panel
chmod -R 775 /var/www/time-vault-panel/storage
chmod -R 775 /var/www/time-vault-panel/bootstrap/cache
```

---

## Step 15 — Configure Nginx

```bash
nano /etc/nginx/sites-available/time-vault
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/time-vault-panel/public;

    index index.php index.html;

    charset utf-8;
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:

```bash
ln -s /etc/nginx/sites-available/time-vault /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
```

---

## Step 16 — Install SSL (Let's Encrypt)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Verify auto-renewal:

```bash
certbot renew --dry-run
```

---

## Step 17 — Setup Queue Worker (Supervisor)

```bash
nano /etc/supervisor/conf.d/time-vault-worker.conf
```

Paste:

```ini
[program:time-vault-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/time-vault-panel/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/time-vault-panel/storage/logs/worker.log
stopwaitsecs=3600
```

Start it:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start time-vault-worker:*
```

---

## Step 18 — Setup Cron (Scheduler)

```bash
crontab -e
```

Add this line:

```
* * * * * cd /var/www/time-vault-panel && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 19 — Optimize for Production

```bash
cd /var/www/time-vault-panel

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Step 20 — Configure Firewall

```bash
apt install -y ufw
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable
ufw status
```

---

## Step 21 — Verify Deployment

```bash
# Check all services are running
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mysql
systemctl status redis
supervisorctl status

# Check application
cd /var/www/time-vault-panel
php artisan about
```

Visit `https://yourdomain.com` — you should see the CRM login page.

---

## DNS Setup (Hostinger Panel)

Point your domain's **A record** to your VPS IP address:

| Type | Name | Value       |
|------|------|-------------|
| A    | @    | YOUR_VPS_IP |
| A    | www  | YOUR_VPS_IP |

Wait 5–10 minutes for DNS propagation, then install SSL (Step 16).

---

## Post-Deploy Commands

| Task                | Command                                         |
|---------------------|-------------------------------------------------|
| Clear all cache     | `php artisan optimize:clear`                    |
| Rebuild cache       | `php artisan optimize`                          |
| Run migrations      | `php artisan migrate --force`                   |
| Restart queue       | `supervisorctl restart time-vault-worker:*`     |
| Check logs          | `tail -f storage/logs/laravel.log`              |
| Restart Nginx       | `systemctl restart nginx`                       |
| Restart PHP-FPM     | `systemctl restart php8.3-fpm`                  |

---

## Troubleshooting

### 502 Bad Gateway
```bash
systemctl restart php8.3-fpm
systemctl restart nginx
```

### Permission Denied Errors
```bash
chown -R www-data:www-data /var/www/time-vault-panel/storage
chmod -R 775 /var/www/time-vault-panel/storage
```

### Queue Not Processing
```bash
supervisorctl status
supervisorctl restart time-vault-worker:*
tail -f /var/www/time-vault-panel/storage/logs/worker.log
```

### Database Connection Refused
```bash
systemctl status mysql
mysql -u timevault -p -e "SELECT 1;"
```

### CSS/JS Not Loading
```bash
cd /var/www/time-vault-panel
npm run build
php artisan optimize:clear
```
