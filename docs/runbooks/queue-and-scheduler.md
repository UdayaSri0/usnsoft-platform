# Queue And Scheduler

## Why They Matter

The platform already uses queue-aware notification plumbing and scheduled CMS content processing:

- `BusinessEventDispatched` queues `SendBusinessEventNotificationJob`
- `routes/console.php` schedules `cms:process-scheduled-pages` every minute

That means queue and scheduler processes are part of normal local verification, especially for notification and publish/unpublish behavior.

## Run Queue Workers Locally

```bash
docker compose exec app php artisan queue:work
```

If you prefer a one-off runner:

```bash
docker compose run --rm app php artisan queue:work
```

## Verify Jobs Are Processing

- Watch the queue worker terminal for processed jobs
- Check Redis is healthy: `docker compose logs redis`
- Trigger a workflow that dispatches queued work, then confirm the worker reacts

## Restart Queue Workers

```bash
docker compose run --rm app php artisan queue:restart
```

Then start `queue:work` again if needed.

## Scheduler Basics

Run a local scheduler worker:

```bash
docker compose exec app php artisan schedule:work
```

Manual single-run scheduler trigger:

```bash
docker compose run --rm app php artisan schedule:run
```

Manual direct command for CMS transitions:

```bash
docker compose run --rm app php artisan cms:process-scheduled-pages
```

## Scheduled Publish and Unpublish Logic

Scheduled CMS transitions depend on the scheduler path defined in `routes/console.php`. If scheduled publishing does not happen, the first thing to check is whether `schedule:work` or `schedule:run` is being executed.

## Testing Scheduled Tasks Locally

1. Prepare a CMS version in an approved state
2. Schedule a publish time close to the current time
3. Run `php artisan schedule:run` or keep `schedule:work` running
4. Verify the content changes state as expected

## Local Troubleshooting

- Queue jobs not moving: confirm `queue:work` is running and Redis is healthy
- Scheduled pages not processing: confirm `schedule:work` is running
- State still wrong after fixes: clear caches with `php artisan optimize:clear`
