# Deployment Overview

## Environments

- `local`: Docker Compose (PHP-FPM, Nginx, PostgreSQL, Redis)
- `staging`: VPS or cloud VM, production-like services and data flow
- `production`: VPS-first architecture with S3-compatible object storage and managed backups

## Runtime Components

- PHP-FPM app container
- Nginx web container
- PostgreSQL database
- Redis for cache/queue/session
- Queue worker process for async jobs/notifications

## Storage Strategy

- Laravel filesystem abstraction is the source of truth.
- Development defaults to local disk.
- Production targets S3-compatible object storage.
- Media metadata is stored in `media_assets` and linked via `media_attachments`.

## Backup and Restore Baseline

- Database snapshot backups must be scheduled from day one.
- Object storage backups/replication should align with retention policy.
- Restore procedure validation should be part of staging operations.

## Operational Notes

- Use `php artisan migrate --force` during controlled deployments.
- Queue workers must run continuously in staging/production.
- Environment secrets (`APP_KEY`, DB, Redis, storage keys, superadmin bootstrap values) must be externally managed.
- See also:
  - `docs/deployment/environment-strategy.md`
  - `docs/deployment/staging-vps.md`
  - `docs/deployment/production-vps.md`
  - `docs/runbooks/backup-and-restore.md`
  - `docs/runbooks/health-checks.md`
