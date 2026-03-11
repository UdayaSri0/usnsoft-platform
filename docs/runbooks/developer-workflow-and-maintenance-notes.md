# Developer Workflow And Maintenance Notes

## Day-To-Day Workflow

1. Start from the repo root
2. Run the Docker stack
3. Keep `queue:work` and `schedule:work` available when testing workflow features
4. Use seeded local accounts for role-based UI checks
5. Run tests and an asset build before handing changes off

## Recommended Local Loop

```bash
docker compose up -d
docker compose up -d node
docker compose exec app php artisan queue:work
docker compose exec app php artisan schedule:work
```

In another terminal:

```bash
docker compose run --rm app php artisan test
docker compose run --rm node sh -lc "npm run build"
```

## UI Maintenance Rules

- Reuse the shared tokens and component classes in `resources/css/app.css`
- Prefer existing Blade UI components over adding new one-off utility piles
- Keep public and admin/custom authenticated experiences visually related, but not identical in layout density
- Do not introduce unsafe runtime page-builder behavior

## Architecture Rules

- Keep one codebase
- Respect the Laravel + Blade + Tailwind structure already in the repository
- Preserve policy, middleware, and permission boundaries
- Do not weaken SuperAdmin-only or approval-gated actions for convenience

## Seed And Demo Data Rules

- Demo users are for local or staging-style environments only
- Do not commit real credentials
- If you change seeded public pages or demo roles, update the relevant docs in the same change set

## Documentation Rules

- Update `README.md` and `docs/` when setup steps, commands, seeders, or workflows change
- Keep Docker service names and commands accurate to `compose.yaml`
- Label destructive recovery commands clearly

## When Adding Livewire Or Filament Later

- Align them with the shared design tokens instead of creating an unrelated admin theme
- Keep approval, authorization, and safe content-rendering rules intact
- Avoid package-level CSS hacks when a shared component or token change would solve the problem cleanly
