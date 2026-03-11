# Debugging Guide

Use this guide for common local-development failures.

## App Does Not Boot

Symptoms:

- `http://localhost:8080` returns 500 or blank output
- `docker compose logs app` shows Laravel bootstrap errors

Likely causes:

- Missing `.env`
- Missing app key
- Dependencies not installed
- Migrations not run

Checks and fixes:

```bash
cp .env.example .env
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan migrate --seed
```

If the browser shows the wrong service, a blank `400`, or anything that is clearly not Laravel on the expected local URL, verify the HTTP port is actually free:

```bash
ss -ltnp 'sport = :8080'
```

If another process owns it, change `USNSOFT_HTTP_PORT` and `APP_URL` in `.env`, then recreate `web`:

```bash
docker compose up -d --force-recreate web
```

## Docker Container Exits

Symptoms:

- `docker compose ps` shows `Exited`

Likely causes:

- Image build failure
- Runtime dependency problem
- Invalid command in service startup

Checks and fixes:

```bash
docker compose logs app
docker compose logs web
docker compose logs node
docker compose up -d --build
```

If `web` exits with an nginx upstream error, ensure `app` is already up and then recreate `web`:

```bash
docker compose up -d app
docker compose up -d --force-recreate web
```

## Database Connection Failure

Symptoms:

- `SQLSTATE` connection errors
- `could not translate host name "postgres"`

Likely causes:

- `postgres` is not healthy
- `.env` database values changed incorrectly

Checks and fixes:

```bash
docker compose ps
docker compose logs postgres
docker compose exec postgres psql -U usnsoft -d usnsoft
```

Verify the local `.env` still uses:

- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_DATABASE=usnsoft`
- `DB_USERNAME=usnsoft`
- `DB_PASSWORD=usnsoft_dev_password`

## Vite or Assets Not Loading

Symptoms:

- Unstyled pages
- Console errors for `/build/assets` or `5173`

Likely causes:

- Assets not built
- `node` service not running
- Browser still referencing stale output

Checks and fixes:

```bash
docker compose up -d node
docker compose logs -f node
docker compose run --rm node sh -lc "npm install && npm run build"
```

## Styles Not Updating

Symptoms:

- Blade changes render, CSS changes do not

Likely causes:

- Vite dev server not running
- Built assets are stale

Fix steps:

1. Start `node` with `docker compose up -d node`
2. Or rebuild production assets with `docker compose run --rm node sh -lc "npm run build"`
3. Hard refresh the browser

## 403 or Authorization Issues

Symptoms:

- You can log in but a route returns 403

Likely causes:

- Wrong seeded role
- Using an unverified or non-internal account for an internal route
- Expected permission missing from role

Checks and fixes:

1. Confirm the account role in [../access/sample-logins.md](../access/sample-logins.md)
2. Re-seed roles and demo users if needed
3. Use `superadmin@usnsoft.test` for top-tier admin checks

## 419 CSRF or Session Issues

Symptoms:

- Form submission returns 419

Likely causes:

- Session table missing
- Stale cookies
- Cached config mismatch

Fix steps:

```bash
docker compose run --rm app php artisan optimize:clear
docker compose run --rm app php artisan migrate
```

Then log out, clear cookies for `localhost`, and retry.

## 500 Internal Server Error

Symptoms:

- Unexpected exception after page load or form submit

Checks:

```bash
docker compose logs -f app
```

Safe fixes:

```bash
docker compose run --rm app php artisan optimize:clear
docker compose run --rm app composer dump-autoload
```

## Queue Jobs Not Running

Symptoms:

- Notifications or queued work never complete

Likely causes:

- Queue worker not running
- Redis not available

Checks and fixes:

```bash
docker compose logs redis
docker compose exec app php artisan queue:work
docker compose run --rm app php artisan queue:restart
```

## Scheduled Tasks Not Running

Symptoms:

- Scheduled CMS publish/archive actions do not happen

Likely causes:

- Scheduler worker not running

Checks and fixes:

```bash
docker compose exec app php artisan schedule:work
docker compose run --rm app php artisan schedule:run
docker compose run --rm app php artisan cms:process-scheduled-pages
```

## File Upload Issues

Symptoms:

- Uploaded files not visible
- Broken profile image or storage-backed asset paths

Likely causes:

- Missing `storage:link`
- Filesystem config mismatch

Fix:

```bash
docker compose run --rm app php artisan storage:link
```

## Mail Not Sending In Local

Symptoms:

- Password reset or verification mail seems to vanish

Likely causes:

- Mailpit not running
- Mail config changed away from Mailpit

Checks:

```bash
docker compose ps
docker compose logs mailpit
```

Then open `http://localhost:8025`.

## Sample Login Not Working

Symptoms:

- Seeded account email/password rejected

Likely causes:

- Demo users never seeded
- Database was reset without demo seeding enabled

Fix:

```bash
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
```

## Permission or Role Mismatch

Symptoms:

- Account can log in but expected menu items or routes are missing

Likely causes:

- Role permissions not seeded correctly

Fix:

```bash
docker compose run --rm app php artisan db:seed --class=RolePermissionSeeder
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
```

## Seeders Failing

Symptoms:

- `migrate --seed` stops during seeding

Likely causes:

- Earlier schema failure
- Partial seed state

Fix:

1. Review the app logs/output
2. If the database is inconsistent, run `migrate:fresh --seed`

## Broken Migration Order

Symptoms:

- Table or column missing errors during migrate

Checks:

- Review `database/migrations/` ordering
- Confirm no migration file was renamed incorrectly

Fix:

```bash
docker compose run --rm app php artisan migrate:fresh --seed
```

## Stale Caches

Symptoms:

- Routes/config/views behave differently from current code

Fix:

```bash
docker compose run --rm app php artisan optimize:clear
```

## Missing Storage Symlink

Symptoms:

- `/storage/...` URLs do not resolve

Fix:

```bash
docker compose run --rm app php artisan storage:link
```

## Route/Model Binding Issues

Symptoms:

- Specific route returns 404 unexpectedly

Likely causes:

- Wrong slug/path in seeded CMS pages
- Route parameter mismatch

Fix:

1. Re-seed CMS system pages
2. Check `routes/web.php`
3. Verify current database values for `pages.path_current`

## Livewire/Filament Asset Problems

Current repo state:

- The architecture is prepared for Livewire/Filament alignment, but this repository currently uses custom Blade admin surfaces rather than an installed Filament package.

If you later add Filament or Livewire:

1. Make sure their asset pipeline is installed correctly
2. Keep the shared tokens and interaction patterns aligned with `resources/css/app.css`
3. Avoid one-off CSS overrides that fork the admin look away from the shared design system
