# Database Reset And Seeding

## Local Migration Strategy

Use normal migrations when the database is already in a sane state:

```bash
docker compose run --rm app php artisan migrate
```

Use a fresh reset only when the local database is broken, disposable, or badly out of sync:

```bash
docker compose run --rm app php artisan migrate:fresh --seed
```

`migrate:fresh --seed` is destructive.

## What `migrate --seed` and `migrate:fresh --seed` Create

Core seed flow in `DatabaseSeeder`:

- `CoreRoleSeeder`
- `PermissionScaffoldSeeder`
- `RolePermissionSeeder`
- `SuperAdminBootstrapSeeder`
- `CmsBlockDefinitionSeeder`
- `CmsSystemPageSeeder`

Optional automatic local demo users:

- `LocalDevelopmentSeeder`  
  Only auto-runs when `APP_ENV` is `local` or `staging` and `USNSOFT_SEED_DEMO_USERS=true`

## Reseed Demo Data

Demo accounts only:

```bash
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
```

System pages only:

```bash
docker compose run --rm app php artisan db:seed --class=CmsSystemPageSeeder
```

Roles and permissions only:

```bash
docker compose run --rm app php artisan db:seed --class=RolePermissionSeeder
```

## Recovering From Partial Or Failed Seeds

If a seeder failed partway through:

1. Check the app output/logs
2. Re-run the specific seeder if the database is still coherent
3. If the local database is dirty or inconsistent, use `migrate:fresh --seed`

## Verifying Seeded Data

Quick checks:

- Open the public site at the `APP_URL` from `.env` (default `http://localhost:8080`)
- Open `/about`, `/services`, `/products`, `/blog`, `/faq`, `/client-request`
- Log in with a demo account from [../access/sample-logins.md](../access/sample-logins.md)
- Open `/admin/cms/pages` and confirm the system pages exist

Database checks:

```bash
docker compose exec postgres psql -U usnsoft -d usnsoft
```

Then run:

```sql
SELECT key, path_current, title_current FROM pages ORDER BY path_current;
SELECT email, status, is_internal FROM users ORDER BY email;
```
