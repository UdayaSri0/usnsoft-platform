# Local Development

This repository is Docker-first. Use the commands below from the project root.

## Prerequisites

- Docker Engine
- Docker Compose plugin (`docker compose`)
- A free HTTP port for the app, default `8080`, plus `5173`, `5432`, `6379`, and `8025`

## Repository Bootstrap

1. Clone the repository.
2. Enter the project directory.
3. Copy the environment file:

```bash
cp .env.example .env
```

4. Optional but recommended for local QA: open `.env` and set:

```dotenv
USNSOFT_HTTP_PORT=8080
USNSOFT_SEED_DEMO_USERS=true
```

If `8080` is already in use on your machine, choose another free value such as `8088` and keep `APP_URL` aligned with it.

5. Start the full stack:

```bash
docker compose up -d --build
```

## Install Dependencies

Install PHP dependencies inside the `app` service:

```bash
docker compose run --rm app composer install
```

The `node` service installs npm packages automatically when it starts, but for a one-off install/build you can also run:

```bash
docker compose run --rm node sh -lc "npm install && npm run build"
```

## Laravel Bootstrap

Generate the application key:

```bash
docker compose run --rm app php artisan key:generate
```

Run migrations and seeders:

```bash
docker compose run --rm app php artisan migrate --seed
```

Create the storage symlink:

```bash
docker compose run --rm app php artisan storage:link
```

## Start Supporting Processes

Start the Vite dev server:

```bash
docker compose up -d node
```

Run the queue worker:

```bash
docker compose exec app php artisan queue:work
```

Run the scheduler worker:

```bash
docker compose exec app php artisan schedule:work
```

The scheduler is important because `routes/console.php` registers `cms:process-scheduled-pages` to run every minute.

## Access URLs

- Application: `APP_URL` from `.env` (default `http://localhost:8080`)
- Mailpit: `http://localhost:8025`
- Vite dev server: `http://localhost:5173`
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`

Useful service names in `compose.yaml`:

- `app`
- `web`
- `postgres`
- `redis`
- `node`
- `mailpit`

## Database and Mail Access

Open a PostgreSQL shell:

```bash
docker compose exec postgres psql -U usnsoft -d usnsoft
```

Mailpit captures local mail at `http://localhost:8025`. No real SMTP credentials are required for local development with the default `.env.example`.

## Sample Accounts

If `USNSOFT_SEED_DEMO_USERS=true` was set before `migrate --seed`, the development accounts are created automatically. See [../access/sample-logins.md](../access/sample-logins.md).

If you forgot to enable the flag, you can still seed the accounts manually later:

```bash
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
```

## Common First-Run Issues

- Missing app key  
  Run `docker compose run --rm app php artisan key:generate`
- `localhost:8080` shows the wrong service or a blank `400`  
  Set `USNSOFT_HTTP_PORT` to a free port in `.env`, update `APP_URL`, then recreate `web`
- Database connection failure  
  Ensure `postgres` is healthy: `docker compose ps`
- CSS or JS not loading  
  Start `node` or run a production asset build
- Demo users missing  
  Confirm `APP_ENV=local` and seed `LocalDevelopmentSeeder`
- Login works but protected routes fail  
  Use a verified seeded account or verify the email for a custom account
