# Docker Commands

Use these from the project root.

## Start and Stop

```bash
docker compose up -d
docker compose up -d --build
docker compose down
docker compose down -v
```

`down -v` is destructive. It removes the PostgreSQL and Redis volumes.

## Rebuild or Restart a Service

```bash
docker compose up -d --build app
docker compose restart app
docker compose restart web
docker compose restart node
```

## Shell Access

```bash
docker compose exec app bash
docker compose exec postgres sh
docker compose exec redis sh
```

## Logs

```bash
docker compose logs -f
docker compose logs -f app
docker compose logs -f web
docker compose logs -f node
docker compose logs -f postgres
```

## Artisan, Composer, and npm

```bash
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan migrate --seed
docker compose run --rm app php artisan test
docker compose run --rm app composer install
docker compose run --rm app composer dump-autoload
docker compose run --rm node sh -lc "npm install && npm run build"
docker compose run --rm node sh -lc "npm install && npm run dev -- --host"
```

## Cache and Runtime Helpers

```bash
docker compose run --rm app php artisan optimize:clear
docker compose run --rm app php artisan config:clear
docker compose run --rm app php artisan route:clear
docker compose run --rm app php artisan view:clear
docker compose run --rm app php artisan queue:restart
docker compose exec app php artisan queue:work
docker compose exec app php artisan schedule:work
docker compose run --rm app php artisan schedule:run
```

## Database Helpers

```bash
docker compose exec postgres psql -U usnsoft -d usnsoft
docker compose run --rm app php artisan migrate:fresh --seed
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
./scripts/ops/backup-postgres.sh
./scripts/ops/restore-postgres.sh path/to/backup.sql.gz
```

## Asset and Storage Helpers

```bash
docker compose run --rm app php artisan storage:link
docker compose up -d node
docker compose run --rm node sh -lc "npm run build"
./scripts/ops/backup-storage.sh
```
