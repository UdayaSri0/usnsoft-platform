# USNsoft Platform

USNsoft is a Laravel 12 single-codebase platform for a polished public website, authenticated customer access, and internal admin/CMS workflows. The stack stays Docker-first, security-aware, and approval-friendly: Blade + Tailwind on the UI side, PostgreSQL + Redis underneath, and CMS/public content rendered through safe structured blocks rather than unsafe runtime page-builder behavior.

## Stack

- Laravel 12
- PHP 8.3+ application requirement, PHP 8.4 in the local Docker image
- PostgreSQL 17
- Redis 7
- Blade + Tailwind CSS + Alpine
- Queue and scheduler support for notifications and scheduled CMS publishing

## Quick Start

1. Copy `.env.example` to `.env`.
2. Set `USNSOFT_SEED_DEMO_USERS=true` in `.env` if you want local sample accounts.
3. If `8080` is already used on your machine, set `USNSOFT_HTTP_PORT` in `.env` to a free port such as `8088` and keep `APP_URL` aligned with it.
4. Start the stack: `docker compose up -d --build`
5. Install dependencies and bootstrap the app:
   - `docker compose run --rm app composer install`
   - `docker compose run --rm app php artisan key:generate`
   - `docker compose run --rm app php artisan migrate --seed`
   - `docker compose run --rm app php artisan storage:link`
6. Start supporting processes as needed:
   - `docker compose up -d node`
   - `docker compose exec app php artisan queue:work`
   - `docker compose exec app php artisan schedule:work`

App URLs:

- Application: `APP_URL` from `.env` (default `http://localhost:8080`)
- Vite dev server: `http://localhost:5173`
- Mailpit: `http://localhost:8025`
- PostgreSQL: `localhost:5432`
- Redis: `localhost:6379`

## Where To Start

- Docs index: [docs/README.md](docs/README.md)
- Local setup: [docs/setup/local-development.md](docs/setup/local-development.md)
- First-run checklist: [docs/setup/first-run-checklist.md](docs/setup/first-run-checklist.md)
- Sample logins: [docs/access/sample-logins.md](docs/access/sample-logins.md)
- Project structure: [docs/architecture/project-structure-overview.md](docs/architecture/project-structure-overview.md)
- UI/theme summary: [docs/ui/ui-audit-and-theme-summary.md](docs/ui/ui-audit-and-theme-summary.md)

## Demo Accounts

Demo accounts are development-only and are created by `LocalDevelopmentSeeder`. The shared demo password is `ChangeMe123!Secure` unless you change the seeder. See [docs/access/sample-logins.md](docs/access/sample-logins.md) for the current list and reset flow.

## Operational Notes

- `LocalDevelopmentSeeder` only runs automatically when the app environment is `local` or `staging` and `USNSOFT_SEED_DEMO_USERS=true`.
- `SuperAdminBootstrapSeeder` is separate and uses `USNSOFT_SUPERADMIN_*` environment values when you explicitly want a bootstrapped privileged account.
- Scheduled CMS publish/archive logic is registered in `routes/console.php` as `cms:process-scheduled-pages`.
- Queue-backed notification plumbing exists even where delivery channels are still placeholders.

## Testing

- Run the test suite with `docker compose run --rm app php artisan test`

## Legacy Reference Docs

Older stage-by-stage notes still exist in `docs/` for deeper context, but new contributors should start with [docs/README.md](docs/README.md) first.
