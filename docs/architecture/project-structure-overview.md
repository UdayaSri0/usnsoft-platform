# Project Structure Overview

This is the shortest useful map for navigating the repository.

## `app/`

Core application code.

Important areas:

- `app/Http/Controllers/`  
  Route controllers for auth, profile, account, and other web flows
- `app/Http/Middleware/`  
  Access control and request lifecycle middleware
- `app/Services/`  
  Cross-cutting application services
- `app/Enums/`  
  Shared enums for roles, approval states, account states, and more
- `app/View/Components/`  
  PHP-backed view components if/when needed

## `app/Modules/`

Domain-oriented modules such as:

- `IdentityAccess`
- `Pages`
- `Notifications`
- `AuditSecurity`
- `Products`
- `ClientRequests`
- `Services`
- `Blog`
- `Careers`

The repository keeps these modules in one codebase rather than splitting them across repos.

## `resources/views/`

Blade templates for:

- public site shells and CMS-rendered pages
- auth screens
- account/profile screens
- admin/custom internal screens
- shared Blade components

## Filament / Livewire Areas

Current repo state:

- The architecture is intended to stay compatible with Livewire and Filament patterns
- This repository currently uses custom Blade/Tailwind admin surfaces rather than an installed Filament package

When Filament or Livewire are added later, keep them aligned with the shared design tokens instead of creating a separate theme.

## `routes/`

- `web.php` for application routes
- `auth.php` for authentication routes
- `console.php` for Artisan commands and scheduled tasks

## `config/`

Framework and platform configuration, including:

- `auth.php`
- `database.php`
- `queue.php`
- `cache.php`
- `mail.php`
- `cms.php`
- `permissions.php`
- `security.php`

## `database/migrations/`

Database schema history for users, roles/permissions, audit/security, media, workflow, and CMS tables.

## `database/seeders/`

Important seeders:

- `DatabaseSeeder`
- `RolePermissionSeeder`
- `LocalDevelopmentSeeder`
- `SuperAdminBootstrapSeeder`
- `CmsBlockDefinitionSeeder`
- `CmsSystemPageSeeder`

## `docs/`

Developer onboarding, runbooks, UI notes, and architecture guidance.

## `docker/`

Container definitions:

- `docker/php/Dockerfile`
- `docker/nginx/default.conf`

## `public/build/assets`

Generated Vite build output after `npm run build`. This is runtime asset output, not a place for source edits.

## `tests/`

- `tests/Feature/` for web/behavior tests
- `tests/Unit/` for lower-level logic tests

Use `docker compose run --rm app php artisan test` to run the test suite.
