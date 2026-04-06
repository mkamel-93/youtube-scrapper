# Docker Setup

## First Time Setup

```bash
# Bootstrap: copy .env and build all containers from scratch
make rebuild-container
```

This creates a fresh `.env` file and builds all Docker containers (PHP, Nginx, MySQL, Redis, MailHog, phpMyAdmin, Selenium).

> **Note:** File permissions are automatically handled by the containers. Both `php` and `web` containers configure proper ownership (`www-data`, `nginx`) and permissions (`775`) for storage, bootstrap/cache, and vendor directories on startup.

## Build & Run

| Command | Description |
|---------|-------------|
| `make rebuild-container` | Destroy and rebuild everything from scratch |
| `make build` | Build containers and start |
| `make up` | Start existing containers |
| `make down` | Stop containers |
| `make restart` | Restart containers |
| `make destroy` | Destroy containers, images, and volumes |

## Run Commands in Containers

```bash
# PHP container (Laravel/Composer commands)
make php-bash
# Then run: php artisan <command>
# Or: COMPOSER=composer.script.json composer run-script <script>

# Web container (Node/npm commands)
make web-bash
# Then run: npm run dev
# Or: npm run build

# Database (MySQL CLI)
make database-bash
```

### Direct Command Execution

```bash
docker compose -f .docker/docker-compose.yml --env-file .env --project-directory . exec --user www-data php php artisan migrate
docker compose -f .docker/docker-compose.yml --env-file .env --project-directory . exec --user nginx web npm run build
```

## Logs

```bash
make logs          # All logs
make logs-watch    # Follow logs
make log-php       # PHP container only
```

## Available Services
Containers communicate via a custom bridge network (`app_subnet`) using **static IP addresses** instead of DNS names:

| Service | Container | IP Address | Access                         |
|---------|-----------|----------|--------------------------------|
| PHP/App | `{APP_NAME}_php` | `172.19.10.11` | `make php-bash`                |
| Web/Nginx | `{APP_NAME}_web` | `172.19.10.12` | Port 80, 5173                  |
| MySQL | `{APP_NAME}_database` | `172.19.10.13` | Port 3306                      |
| phpMyAdmin | `{APP_NAME}_phpmyadmin` | `172.19.10.14` | Port 80                        |
| MailHog | `{APP_NAME}_mailhog` | `172.19.10.15` | Port 8025 (UI)                 |
| Redis | `{APP_NAME}_redis` | `172.19.10.16` | Port 6379                      |

This ensures reliable inter-container communication for services like:
- PHP connecting to MySQL (`DB_HOST=172.19.10.13` || `DB_HOST=mysql` service name)
- PHP connecting to Redis (`REDIS_HOST=172.19.10.16` || `REDIS_HOST=redis` service name)
