# Staging VPS Deployment

## Goals

- Match production behavior closely enough to catch auth, queue, storage, and approval regressions.
- Stay inexpensive and simple to operate.

## Recommended Layout

- 1 VPS
- Docker Engine + Compose plugin
- Reverse proxy terminating HTTPS
- App containers
  - PHP-FPM app
  - Nginx web
  - PostgreSQL
  - Redis
- Optional external object storage

## Required Settings

- `APP_ENV=staging`
- `APP_DEBUG=false`
- `APP_URL=https://staging.example.com`
- `USNSOFT_ENFORCE_INTERNAL_MFA=true`
- `USNSOFT_ANTI_SPAM_DRIVER=turnstile`
- `SESSION_SECURE_COOKIE=true`
- `USNSOFT_HSTS_ENABLED=true`
- `TRUSTED_PROXIES=*` or the exact reverse-proxy IPs
- `TRUSTED_HOSTS=staging.example.com`

## Deployment Flow

1. Pull the release onto the VPS.
2. Update the environment file.
3. Build or pull the app image.
4. Run `php artisan migrate --force`.
5. Run `php artisan optimize:clear`.
6. Restart queue workers.
7. Run a smoke test:
   - login
   - admin/security center
   - public careers apply form
   - client request submission
   - protected download

## Staging Checks

- Confirm object storage credentials point to the staging bucket/container only.
- Confirm mail routes to a staging-safe inbox or provider sandbox.
- Confirm backups do not overwrite production artifacts.
