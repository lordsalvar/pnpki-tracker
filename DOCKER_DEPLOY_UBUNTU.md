# Docker Deploy on Ubuntu (Cloudflare + Octane)

This guide deploys this Laravel 12 + Filament app on Ubuntu using Docker, with:

- Cloudflare proxy in front of your server
- Laravel Octane (Swoole) for app runtime
- Nginx as reverse proxy to Octane
- MySQL for database

## 1) Ubuntu dependencies

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg lsb-release git

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list >/dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo usermod -aG docker "$USER"
newgrp docker
```

## 2) Clone project

```bash
git clone <your-repository-url> pnpki-tracker
cd pnpki-tracker
```

## 3) Required app dependencies for Octane

If not already installed:

```bash
composer require laravel/octane
php artisan octane:install --server=swoole --no-interaction
```

## 4) Docker files

### 4.1 Dockerfile

Create `Dockerfile` in repo root:

```dockerfile
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl unzip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev \
    libjpeg-dev libfreetype6-dev libwebp-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql bcmath mbstring exif pcntl gd intl zip \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22 /usr/local /usr/local

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
```

### 4.2 docker-compose.yml

Create `docker-compose.yml`:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: pnpki_app
    restart: unless-stopped
    env_file:
      - .env
    depends_on:
      db:
        condition: service_healthy
    volumes:
      - storage_data:/var/www/html/storage
      - cache_data:/var/www/html/bootstrap/cache
    networks:
      - pnpki_net

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: pnpki_queue
    restart: unless-stopped
    env_file:
      - .env
    command: ["php", "artisan", "queue:work", "--tries=1", "--sleep=1"]
    depends_on:
      db:
        condition: service_healthy
    volumes:
      - storage_data:/var/www/html/storage
      - cache_data:/var/www/html/bootstrap/cache
    networks:
      - pnpki_net

  web:
    image: nginx:1.27-alpine
    container_name: pnpki_web
    restart: unless-stopped
    ports:
      - "80:80"
    depends_on:
      - app
    volumes:
      - ./:/var/www/html:ro
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - storage_data:/var/www/html/storage
      - cache_data:/var/www/html/bootstrap/cache
    networks:
      - pnpki_net

  db:
    image: mysql:8.0
    container_name: pnpki_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: pnpki_tracker
      MYSQL_USER: pnpki_user
      MYSQL_PASSWORD: change_this_password
      MYSQL_ROOT_PASSWORD: change_this_root_password
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-uroot", "-pchange_this_root_password"]
      interval: 10s
      timeout: 5s
      retries: 10
    networks:
      - pnpki_net

volumes:
  db_data:
  storage_data:
  cache_data:

networks:
  pnpki_net:
    driver: bridge
```

### 4.3 Nginx config

Create `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://app:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

## 5) .env configuration

Copy and edit:

```bash
cp .env.example .env
```

Set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `DB_CONNECTION=mysql`
- `DB_HOST=db`
- `DB_PORT=3306`
- `DB_DATABASE=pnpki_tracker`
- `DB_USERNAME=pnpki_user`
- `DB_PASSWORD=change_this_password`

Cloudflare PDF settings used by this app:

- `LARAVEL_PDF_DRIVER=cloudflare`
- `CLOUDFLARE_API_TOKEN=...`
- `CLOUDFLARE_ACCOUNT_ID=...`

## 6) Build and start

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

## 7) Cloudflare setup

1. Point your domain/subdomain A record to server public IP.
2. Enable Cloudflare proxy (orange cloud).
3. SSL/TLS mode: **Full (strict)**.
4. On origin server, only expose ports 80/443.
5. Keep `APP_URL` as your `https://` domain.

Optional but recommended:

- Enable Cloudflare WAF and Bot protection.
- Add rate limits for login/admin paths.

## 8) Trusted proxies in Laravel (important with Cloudflare)

Ensure Laravel trusts proxy headers so IP/scheme are correct. In Laravel 12, configure middleware trust in `bootstrap/app.php` (or existing project proxy config) for your deployment setup.

## 9) Update deployment

```bash
git pull
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

## 10) Logs and checks

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f web
docker compose logs -f db
```

Health endpoint:

- `https://your-domain.com/up`

## Notes

- Octane workers keep app in memory; after config/code changes, restart app containers:
  - `docker compose restart app queue`
- This project uses Vite, so production build (`npm run build`) is required (already in Dockerfile).
# Docker Deployment Guide (Ubuntu Server)

This guide explains how to deploy this Laravel 12 + Filament project to an Ubuntu server using Docker.

## 1) Server Dependencies

Install these on Ubuntu (22.04/24.04):

- Docker Engine
- Docker Compose plugin
- Git
- (Optional) `ufw` for firewall setup

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg lsb-release git

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo usermod -aG docker "$USER"
newgrp docker
```

## 2) Clone Project

```bash
git clone <your-repository-url> pnpki-tracker
cd pnpki-tracker
```

## 3) Create Docker Files

### 3.1 `Dockerfile`

Create `Dockerfile` in project root:

```dockerfile
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl unzip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev \
    libjpeg-dev libfreetype6-dev libwebp-dev libpq-dev default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql bcmath mbstring exif pcntl gd intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22 /usr/local /usr/local

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm install \
    && npm run build \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
```

### 3.2 `docker-compose.yml`

Create `docker-compose.yml` in project root:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: pnpki_app
    restart: unless-stopped
    env_file:
      - .env
    volumes:
      - storage_data:/var/www/html/storage
      - cache_data:/var/www/html/bootstrap/cache
    depends_on:
      db:
        condition: service_healthy
    networks:
      - pnpki_net

  web:
    image: nginx:1.27-alpine
    container_name: pnpki_web
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html:ro
      - storage_data:/var/www/html/storage
      - cache_data:/var/www/html/bootstrap/cache
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    networks:
      - pnpki_net

  db:
    image: mysql:8.0
    container_name: pnpki_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: pnpki_tracker
      MYSQL_USER: pnpki_user
      MYSQL_PASSWORD: change_this_password
      MYSQL_ROOT_PASSWORD: change_this_root_password
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-uroot", "-pchange_this_root_password"]
      interval: 10s
      timeout: 5s
      retries: 10
    networks:
      - pnpki_net

volumes:
  db_data:
  storage_data:
  cache_data:

networks:
  pnpki_net:
    driver: bridge
```

### 3.3 Nginx config

Create `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 4) Configure Environment

Create `.env` from `.env.example`:

```bash
cp .env.example .env
```

Set required values:

- `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`
- `DB_CONNECTION=mysql`
- `DB_HOST=db`
- `DB_PORT=3306`
- `DB_DATABASE=pnpki_tracker`
- `DB_USERNAME=pnpki_user`
- `DB_PASSWORD=change_this_password`
- Queue/cache/session drivers as needed (recommended: redis for scale; database is fine for small setups)
- PDF/Cloudflare values if used in your app:
  - `LARAVEL_PDF_DRIVER=cloudflare`
  - `CLOUDFLARE_API_TOKEN=...`
  - `CLOUDFLARE_ACCOUNT_ID=...`

## 5) Build and Start

```bash
docker compose up -d --build
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```

If you use queues:

```bash
docker compose exec app php artisan queue:work
```

(For production, define a separate `queue` service in compose so it stays running.)

## 6) File Permissions

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R ug+rwx storage bootstrap/cache
```

## 7) HTTPS (Recommended)

Use a reverse proxy + TLS (for example, Nginx Proxy Manager, Traefik, or Caddy) in front of this stack.

At minimum:

- Keep app reachable internally on port 80
- Terminate SSL at proxy
- Set `APP_URL` to `https://your-domain`

## 8) Updating the App

```bash
git pull
docker compose up -d --build
docker compose exec app composer install --no-dev --optimize-autoloader --no-interaction
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

## 9) Basic Health Checks

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f web
docker compose logs -f db
```

App endpoint health route (Laravel default in this project): `/up`

## 10) Notes for This Project

- Project uses Laravel 12, Filament 5, PHP 8.4, Vite/Tailwind 4.
- Frontend assets must be built (`npm run build`) for production.
- Some admin pages do frequent counts; database indexes are important for responsiveness.

