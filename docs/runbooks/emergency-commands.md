# Emergency Commands

This is a local/development recovery reference. Read the labels before running anything destructive.

## Safe Commands

Application maintenance mode:

```bash
docker compose run --rm app php artisan down
docker compose run --rm app php artisan up
```

Clear caches and compiled state:

```bash
docker compose run --rm app php artisan cache:clear
docker compose run --rm app php artisan config:clear
docker compose run --rm app php artisan config:cache
docker compose run --rm app php artisan route:clear
docker compose run --rm app php artisan route:cache
docker compose run --rm app php artisan view:clear
docker compose run --rm app php artisan event:clear
docker compose run --rm app php artisan optimize:clear
```

Queue and scheduler helpers:

```bash
docker compose run --rm app php artisan queue:restart
docker compose exec app php artisan queue:work
docker compose exec app php artisan schedule:work
docker compose run --rm app php artisan schedule:run
docker compose run --rm app php artisan cms:process-scheduled-pages
```

Asset and autoload refresh:

```bash
docker compose run --rm node sh -lc "npm run build"
docker compose up -d node
docker compose run --rm app composer dump-autoload
docker compose run --rm app php artisan storage:link
```

Docker inspection:

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f web
docker compose logs -f node
docker compose restart app
docker compose restart web
```

## Use With Caution

Migrations:

```bash
docker compose run --rm app php artisan migrate
docker compose run --rm app php artisan migrate:rollback
```

Rollback is not always safe if later migrations depend on earlier schema changes. Check the migration order first.

## Destructive Commands

Full local database reset:

```bash
docker compose run --rm app php artisan migrate:fresh --seed
```

Full Docker volume reset:

```bash
docker compose down -v
```

Both commands destroy local data. Do not run them casually.

## Recovering From Common Broken States

Broken seed/database state:

1. Try `docker compose run --rm app php artisan migrate --seed`
2. If the schema is inconsistent, use `migrate:fresh --seed`
3. Re-run `LocalDevelopmentSeeder` if demo accounts are missing

Stale compiled assets:

1. `docker compose run --rm node sh -lc "npm run build"`
2. If you are using Vite hot reload, restart the `node` service
3. Clear browser cache only after rebuilding assets

Auth/session weirdness locally:

1. `docker compose run --rm app php artisan optimize:clear`
2. Confirm the `sessions` table exists
3. Log out and log back in
4. If needed, reset the database and re-seed

Permission ownership problems from container commands:

```bash
docker compose run --rm app sh -lc "chown -R 1000:1000 /var/www/html"
```

Use that only when files become root-owned on the host.
