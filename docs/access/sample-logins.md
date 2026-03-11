# Sample Logins

LOCAL DEVELOPMENT ONLY. Do not use these credentials outside local or staging-style demo environments.

The accounts come from `database/seeders/LocalDevelopmentSeeder.php`.

## Default Password

All seeded demo accounts use:

```text
ChangeMe123!Secure
```

## Core Demo Accounts

| Role | Email | Purpose |
| --- | --- | --- |
| SuperAdmin | `superadmin@usnsoft.test` | Full privileged access, approval/publishing override, block definition governance |
| Admin | `admin@usnsoft.test` | Internal admin access for user/content oversight without SuperAdmin-only elevation |
| Editor | `editor@usnsoft.test` | Drafting, composing, previewing, and submitting CMS content for review |
| Product Manager | `productmanager@usnsoft.test` | Product/content coordination with request visibility |
| Sales Manager | `salesmanager@usnsoft.test` | Request status, public-facing communication, and approval queue visibility |
| Developer | `developer@usnsoft.test` | Technical internal access for advanced block/page work and platform QA |
| Support / Operations | `support@usnsoft.test` | Support-oriented internal visibility for security events, request updates, and previews |
| Standard User | `user@usnsoft.test` | Normal authenticated user for profile, request creation, and protected download access |

## Extra QA Accounts

| Account | Email | Purpose |
| --- | --- | --- |
| Unverified User | `unverified-user@usnsoft.test` | Test verification gates |
| Deactivated User | `deactivated-user@usnsoft.test` | Test blocked account behavior |
| Staff Unverified | `staff-unverified@usnsoft.test` | Test internal account verification edge cases |
| Staff Suspended | `staff-suspended@usnsoft.test` | Test suspended internal account behavior |

## How They Are Seeded

Automatic path:

1. Set `USNSOFT_SEED_DEMO_USERS=true` in `.env`
2. Run `docker compose run --rm app php artisan migrate --seed`

Manual reseed path:

```bash
docker compose run --rm app php artisan db:seed --class=LocalDevelopmentSeeder
```

## Full Reset

Destructive:

```bash
docker compose run --rm app php artisan migrate:fresh --seed
```

If you want demo users to come back automatically during `migrate:fresh --seed`, keep `USNSOFT_SEED_DEMO_USERS=true` in `.env`.

## Optional SuperAdmin Bootstrap

There is also a separate `SuperAdminBootstrapSeeder` for explicitly bootstrapping a privileged account from environment variables:

- `USNSOFT_SUPERADMIN_EMAIL`
- `USNSOFT_SUPERADMIN_NAME`
- `USNSOFT_SUPERADMIN_PASSWORD`

Run it manually if needed:

```bash
docker compose run --rm app php artisan db:seed --class=SuperAdminBootstrapSeeder
```
