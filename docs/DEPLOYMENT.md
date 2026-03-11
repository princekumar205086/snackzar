# Snackzar — Deployment Guide

## Server Requirements

- PHP 8.2+ with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, Redis
- MySQL 8.0+
- Redis 6+
- Node.js 18+ (for asset building)
- Nginx or Apache
- Supervisor (for queue workers & Reverb)
- SSL certificate (Let's Encrypt recommended)

---

## Production Deployment

### 1. Server Setup

```bash
# Clone repository
git clone https://github.com/yourusername/snackzar.git /var/www/snackzar
cd /var/www/snackzar

# Set ownership
sudo chown -R www-data:www-data /var/www/snackzar
sudo chmod -R 775 storage bootstrap/cache
```

### 2. Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate

# Edit .env with production values
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://snackzar.com
# DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# REDIS_HOST=127.0.0.1
# CACHE_STORE=redis
# QUEUE_CONNECTION=redis
# SESSION_DRIVER=redis
```

### 4. Database Setup

```bash
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminSeeder --force
# Optionally seed demo data:
# php artisan db:seed --class=DemoSeeder --force
```

### 5. Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache    # If using blade-icons
```

### 6. Generate API Documentation

```bash
php artisan scribe:generate
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name snackzar.com www.snackzar.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name snackzar.com www.snackzar.com;

    root /var/www/snackzar/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/snackzar.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/snackzar.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## Supervisor Configuration

### Queue Worker (Horizon)

```ini
[program:snackzar-horizon]
process_name=%(program_name)s
command=php /var/www/snackzar/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/snackzar/storage/logs/horizon.log
stopwaitsecs=3600
```

### Reverb WebSocket Server

```ini
[program:snackzar-reverb]
process_name=%(program_name)s
command=php /var/www/snackzar/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/snackzar/storage/logs/reverb.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start snackzar-horizon
sudo supervisorctl start snackzar-reverb
```

---

## Scheduled Tasks (Cron)

Add to crontab (`crontab -e` as www-data):

```
* * * * * cd /var/www/snackzar && php artisan schedule:run >> /dev/null 2>&1
```

---

## Maintenance Commands

```bash
# Clear all application caches
php artisan app:clear-cache

# Clear framework caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-optimize
php artisan optimize

# Check Horizon status
php artisan horizon:status

# Restart Horizon after code changes
php artisan horizon:terminate
```

---

## Updating / Zero-Downtime Deploy

```bash
cd /var/www/snackzar

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Rebuild assets
npm ci && npm run build

# Clear and re-cache
php artisan optimize

# Restart workers
php artisan horizon:terminate
sudo supervisorctl restart snackzar-reverb

# Clear app cache
php artisan app:clear-cache
```
