# Docker app + MySQL on the host

This project’s `compose.yaml` runs **only** the Laravel Sail app container (`laravel.test`). **MySQL is not in Compose**; run it on the host (or another machine) and point the app at it.

## What changes conceptually

- Inside a container, `127.0.0.1` is the **container**, not the host. So `DB_HOST=127.0.0.1` usually **does not** reach MySQL on the host.
- Point the app at the host using **`host.docker.internal`** (recommended with Docker Compose) or the **bridge gateway IP** (often `172.17.0.1` on Linux; can vary).

`compose.yaml` maps `host.docker.internal` for `laravel.test` via `extra_hosts` and `host-gateway`.

## 1) MySQL on the server

Install and configure MySQL on the host as you normally would (package or managed install). Then:

### Database and user

Create the database and a dedicated user with a strong password.

PHPUnit is configured to use a database named `testing` (`phpunit.xml`). If `DB_CONNECTION=mysql` when you run tests, create that database on the host and grant your user access (or point tests at SQLite by overriding `DB_CONNECTION` in `phpunit.xml`).

### Listen address (required for Docker bridge)

If MySQL only listens on `127.0.0.1`, connections from the Docker bridge (even to “the host”) may not reach it the way you expect. Typical options:

- Set `bind-address = 0.0.0.0` in MySQL config and **restrict access** with firewall rules (e.g. only allow port `3306` from the Docker bridge subnet, or from `127.0.0.1` if you use host networking for the app—see below).
- Alternatively, use **`network_mode: host`** for the Laravel container so the app shares the host network; then `DB_HOST=127.0.0.1` can work. That has other implications (port conflicts, isolation), so only use it if you understand the tradeoffs.

### User host in MySQL grants

The app will connect **as if from a non-localhost IP** (Docker bridge). Create or adjust the user so it is allowed from that path, for example:

```sql
CREATE USER 'your_user'@'%' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'%';
FLUSH PRIVILEGES;
```

Tighten `%` to your Docker subnet (e.g. `172.17.%`) if your environment allows.

## 2) `.env` on the server

Use MySQL driver settings aimed at the **host**:

```env
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_strong_password
```

Production:

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
- Run `php artisan key:generate` if `APP_KEY` is empty.

If `host.docker.internal` does not resolve in your Docker setup, try the host gateway IP (from the app container: `getent hosts host.docker.internal` or inspect Docker networks) or set `DB_HOST` to that IP explicitly.

## 3) Run Artisan against the running container

Examples (Sail service name `laravel.test`):

```bash
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan optimize
```

## 4) Firewall and security

- Prefer **not** exposing MySQL to the public internet.
- If `bind-address` is `0.0.0.0`, use **host firewall** rules so only trusted interfaces or IPs can reach `3306`.
- Use TLS for MySQL in production if connections cross networks; configure Laravel’s MySQL SSL options in `config/database.php` / env as needed.

## 5) Related docs

For a full production-style Docker layout (Octane, Nginx, etc.), see `DOCKER_DEPLOY_UBUNTU.md`. This file focuses on **MySQL on the host** with the Sail app container in Compose.
