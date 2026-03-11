# First-Run Checklist

Use this as a short ordered checklist after cloning the repository.

1. Copy `.env.example` to `.env`
2. Set `USNSOFT_SEED_DEMO_USERS=true` in `.env` if you want demo accounts
3. If `8080` is already used on your machine, change `USNSOFT_HTTP_PORT` and `APP_URL` in `.env`
4. Start containers with `docker compose up -d --build`
5. Run `docker compose run --rm app composer install`
6. Run `docker compose run --rm app php artisan key:generate`
7. Run `docker compose run --rm app php artisan migrate --seed`
8. Run `docker compose run --rm app php artisan storage:link`
9. Start Vite if you want hot reload: `docker compose up -d node`
10. Start the queue worker: `docker compose exec app php artisan queue:work`
11. Start the scheduler worker: `docker compose exec app php artisan schedule:work`
12. Open the `APP_URL` from `.env` (default `http://localhost:8080`)
13. Verify the homepage loads
14. Verify the admin area opens with a seeded internal account
15. Verify a sample login works from [../access/sample-logins.md](../access/sample-logins.md)
16. Verify Mailpit opens at `http://localhost:8025`
17. Check logs if anything looks wrong: `docker compose logs -f app`

If any step fails, go to [../runbooks/debugging-guide.md](../runbooks/debugging-guide.md).
