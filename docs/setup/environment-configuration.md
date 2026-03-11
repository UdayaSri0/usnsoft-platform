# Environment Configuration

The application uses `.env` for local runtime configuration. Start from `.env.example` and never commit real secrets.

## How `.env` Is Used

- Laravel reads `.env` inside the `app` container
- The same repository-local `.env` file is mounted into the Docker services
- `USNSOFT_HTTP_PORT` controls the host-side HTTP port exposed by Docker
- `APP_URL` should match that port for local development, defaulting to `http://localhost:8080`

## Important Variables

Core app:

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_DEBUG`
- `USNSOFT_HTTP_PORT`
- `APP_URL`

Database:

- `DB_CONNECTION=pgsql`
- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_DATABASE=usnsoft`
- `DB_USERNAME=usnsoft`
- `DB_PASSWORD=usnsoft_dev_password`

Redis / cache / queue / session:

- `QUEUE_CONNECTION=redis`
- `CACHE_STORE=redis`
- `REDIS_HOST=redis`
- `REDIS_PORT=6379`
- `SESSION_DRIVER=database`

Mail:

- `MAIL_MAILER=smtp`
- `MAIL_HOST=mailpit`
- `MAIL_PORT=1025`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

Platform-specific:

- `USNSOFT_SUPERADMIN_EMAIL`
- `USNSOFT_SUPERADMIN_NAME`
- `USNSOFT_SUPERADMIN_PASSWORD`
- `USNSOFT_ENFORCE_INTERNAL_MFA`
- `USNSOFT_SUSPICIOUS_LOGIN_THRESHOLD`
- `USNSOFT_SEED_DEMO_USERS`

OAuth placeholders:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI`

## Local vs Staging vs Production

Local:

- Keep `APP_ENV=local`
- `APP_DEBUG=true`
- Use Mailpit
- Use local demo accounts only

Staging:

- Use staging-specific credentials and URLs
- Keep demo data optional and clearly isolated
- Do not reuse local secrets

Production:

- No demo accounts
- No placeholder OAuth secrets
- No local development passwords
- Review `APP_DEBUG`, mail, queue, storage, and security settings carefully

## Mail Configuration

The default local setup uses Mailpit. Do not put real SMTP usernames or passwords into committed files.

If you move away from Mailpit later, configure:

- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`

## Database Configuration

The Docker stack expects PostgreSQL through the `postgres` service. Keep the host as `postgres` inside containers.

Do not hardcode production credentials in:

- `.env.example`
- seeders
- docs
- test fixtures

## Redis, Cache, and Session Notes

- Queue jobs use Redis by default
- Cache uses Redis by default
- Sessions use the `database` driver even in local

That means migrations must run successfully before normal authenticated flows behave correctly.

## File Storage Notes

- `FILESYSTEM_DISK=local` in `.env.example`
- Run `php artisan storage:link` after bootstrap
- If you later move to S3-compatible storage, keep credentials out of source control

## Security Rules

Never commit:

- real API keys
- production SMTP credentials
- production database passwords
- private OAuth secrets
- copied `.env` files

Use placeholders in docs and `.env.example`, and keep real secrets in environment management outside git.
