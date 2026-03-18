# USNsoft Local Run Guide

This project is Docker-first. Use these commands from the project root.

## 1) Prerequisites

- Docker Engine
- Docker Compose plugin (`docker compose`)

## 2) First-Time Setup

```bash
# 1. Start containers
docker compose up -d --build

# 2. Install PHP dependencies
docker compose run --rm app composer install

# 3. Create .env from example (if not already present)
cp .env.example .env

# 4. Generate app key (safe to run once)
docker compose run --rm app php artisan key:generate

# 5. Run migrations and seed core data
docker compose run --rm app php artisan migrate --seed

# 6. Build frontend assets
docker compose run --rm node sh -lc "npm install && npm run build"
```

Open:

- App: `APP_URL` from `.env` (default `http://localhost:8088`)
- Mailpit: `http://localhost:8025`

## 3) Daily Run

```bash
# Start all services
docker compose up -d

# Queue worker (keep this running in a separate terminal)
docker compose exec app php artisan queue:work
```

Optional frontend hot-reload:

```bash
docker compose up -d node
```

Vite dev server URL: `http://localhost:5173`

## 4) After Pulling New Changes

If the repository has new migrations or seed updates, run them before opening the app:

```bash
# Apply new schema changes without resetting existing local data
docker compose run --rm app php artisan migrate

# Refresh local demo/catalog content if the pull included new seed data
docker compose run --rm app php artisan db:seed
```

If the browser is still serving an old compiled view after a pull, clear Blade's compiled output:

```bash
docker compose exec app php artisan view:clear
```

## 5) Run Tests

```bash
docker compose run --rm app php artisan test
```

## 6) Useful Commands

```bash
# Re-run migrations and seeders
docker compose run --rm app php artisan migrate:fresh --seed

# Seed local/staging demo users (if enabled in .env)
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder

# Stop services
docker compose down

# Stop and remove DB/Redis volumes (full reset)
docker compose down -v
```

## 7) Optional Bootstraps

### SuperAdmin bootstrap

Set these in `.env`:

- `USNSOFT_SUPERADMIN_EMAIL`
- `USNSOFT_SUPERADMIN_NAME`
- `USNSOFT_SUPERADMIN_PASSWORD`

Then run:

```bash
docker compose run --rm app php artisan db:seed --class=SuperAdminBootstrapSeeder
```

### Google OAuth scaffold

Set these in `.env`:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` (default: `${APP_URL}/auth/google/callback`)

### Demo users (safe local/staging only)

Set in `.env`:

- `USNSOFT_SEED_DEMO_USERS=true`

Then run:

```bash
docker compose run --rm app php artisan db:seed
```

Demo password for seeded users:

- `ChangeMe123!Secure`

## 8) Database Access (PostgreSQL)

```bash
# Open a psql shell
docker compose exec postgres psql -U usnsoft -d usnsoft
```

Useful psql commands:

```sql
\dt
\d users
SELECT id, email, status, is_internal FROM users LIMIT 20;
SELECT id, event_type, severity, occurred_at FROM security_events ORDER BY id DESC LIMIT 20;
```

## 9) Common Troubleshooting

If files become root-owned after running container commands:

```bash
docker compose run --rm app sh -lc "chown -R 1000:1000 /var/www/html"
```
