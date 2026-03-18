# Production VPS Deployment

## Scope

This phase assumes a cost-conscious single-region VPS deployment, not Kubernetes or a managed platform.

## Minimum Runtime

- Reverse proxy with HTTPS
- App container (PHP-FPM)
- Web container (Nginx)
- PostgreSQL
- Redis
- Continuous queue worker
- Continuous scheduler trigger

## Production Environment Expectations

- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_LEVEL=info` or stricter
- `SESSION_SECURE_COOKIE=true`
- `USNSOFT_ENFORCE_INTERNAL_MFA=true`
- `USNSOFT_ANTI_SPAM_DRIVER=turnstile`
- `USNSOFT_HSTS_ENABLED=true`
- `TRUSTED_PROXIES` and `TRUSTED_HOSTS` configured correctly

## Object Storage

- Prefer separate public and private prefixes or buckets.
- Keep applicant files, project request attachments, and protected downloads on private storage.
- Do not expose raw protected object URLs publicly.
- Keep Laravel authorization as the source of truth for protected access.

## Mail

- Use SMTP or a provider-backed transport.
- Queue operational mail instead of sending inline during user-facing requests.
- Keep from-address and bounce settings environment-driven.

## Queue And Scheduler

- Queue worker must be supervised and restarted automatically.
- Scheduler must run every minute via `schedule:run` or a long-lived worker.
- After deploys, run `php artisan queue:restart`.

## Production Smoke Test

- public home page
- login
- MFA challenge for staff
- admin dashboard
- security center
- protected download
- applicant file download by authorized staff
- queue worker receives a notification job

## Must-Do Before Go-Live

- Test database restore on a non-production target.
- Verify object storage keys and bucket policies.
- Confirm anti-spam is active on public forms.
- Confirm backups are scheduled and retention is documented.
