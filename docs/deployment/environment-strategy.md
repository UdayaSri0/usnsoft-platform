# Environment Strategy

## Environments

- `local`
  - Docker Compose only
  - `APP_DEBUG=true`
  - `USNSOFT_ANTI_SPAM_DRIVER=null`
  - local/private filesystem disks by default
  - Mailpit for mail capture
  - Redis-backed queues and sessions
- `staging`
  - production-like VPS deployment
  - `APP_DEBUG=false`
  - `USNSOFT_ENFORCE_INTERNAL_MFA=true`
  - real anti-spam provider enabled
  - object storage preferred for media and protected files
  - SMTP/provider mailer with queued delivery
- `production`
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `USNSOFT_ENFORCE_INTERNAL_MFA=true`
  - HTTPS termination, trusted proxies/hosts configured
  - object storage for media
  - queue workers and scheduler always on
  - backup scripts/runbooks scheduled and tested

## Configuration Discipline

- Keep `.env.example` secret-free.
- Store real secrets only in environment files outside version control or your process manager.
- Use the same variable names across staging and production to reduce drift.
- Set `TRUSTED_PROXIES` and `TRUSTED_HOSTS` in staging/production once the reverse proxy hostnames are known.

## Environment Differences

- Mail
  - local: Mailpit
  - staging/production: SMTP or provider-backed mailer
- Anti-spam
  - local: null provider
  - staging/production: Turnstile or future provider
- Storage
  - local: filesystem
  - staging/production: S3-compatible private/public buckets where possible
- Security headers
  - local: enabled, but HSTS off
  - staging/production: enabled, HSTS on only behind real HTTPS

## Safe Promotion Notes

- Restore production data into staging only after sanitizing user-facing secrets and real contact inboxes where appropriate.
- Never point staging at production mailboxes, buckets, or webhooks.
- Validate backups, queue workers, scheduler, and MFA enforcement in staging before every production release.
