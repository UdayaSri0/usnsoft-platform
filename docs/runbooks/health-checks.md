# Health Checks And Smoke Tests

## Built-In Health Endpoint

- Laravel exposes `/up`

Use it for a basic runtime check, not as the only production signal.

## Operational Checks

- app responds over HTTPS
- database connection works
- Redis is reachable
- queue worker is consuming jobs
- scheduler is running
- storage is writable where expected
- login and MFA challenge succeed
- protected downloads still stream without raw path leakage

## Recommended Commands

```bash
docker compose ps
docker compose logs --tail=100 app
docker compose logs --tail=100 web
docker compose logs --tail=100 redis
docker compose exec app php artisan queue:failed
docker compose exec app php artisan schedule:list
curl -I https://example.com/up
```

## Release Smoke Test

1. Open `/up`.
2. Log in with a verified user.
3. Log in with an internal staff account and complete the MFA challenge.
4. Submit a careers application in staging/local.
5. Submit or view a client request.
6. Download a protected product asset.
7. Check the security center for recent events.
