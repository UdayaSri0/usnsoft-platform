# Backup And Restore

## What To Back Up

- PostgreSQL database
- local media/protected storage if you are not yet on object storage
- object storage bucket/container backups or replication if object storage is enabled

## Database Backup

Docker-based backup script:

```bash
./scripts/ops/backup-postgres.sh
```

Optional target directory:

```bash
./scripts/ops/backup-postgres.sh /var/backups/usnsoft/database
```

## Local Storage Backup

```bash
./scripts/ops/backup-storage.sh
```

This archives:

- `storage/app/private`
- `storage/app/public`

If production uses S3-compatible object storage, use provider-native backup/replication for the bucket as the primary media backup path.

## Restore Database

```bash
./scripts/ops/restore-postgres.sh storage/app/backups/database/postgres-20260318T120000Z.sql.gz
```

Warnings:

- confirm the target database and environment first
- take a fresh backup before restoring over an existing environment
- do not restore production data into staging without sanitization planning

## Restore Order

1. Validate environment file and storage credentials.
2. Restore the database.
3. Restore local storage or confirm object storage snapshot availability.
4. Run `php artisan migrate --force` only if the target release expects newer schema changes.
5. Run `php artisan optimize:clear`.
6. Verify queues, scheduler, downloads, and applicant/request files.

## Retention Guidance

- keep daily database backups
- keep multiple restore points
- archive old backups before deletion if policy requires long-term retention
- never silently discard audit/security data; archive it intentionally later
