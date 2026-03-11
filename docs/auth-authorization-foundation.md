# USNsoft Stage 1 Auth + Authorization Foundation

## Scope Implemented

- Public auth: email/password, password reset, email verification.
- OAuth scaffold: Google sign-in via `laravel/socialite` with safe-linking rules.
- Public registration always creates only `user` role accounts.
- Internal privileged account creation restricted to `super_admin`.
- Role/permission checks enforced by middleware + policies + service-layer checks.
- Security logging baseline: login success/failed/throttled/suspicious, logout, verification, password reset, role/permission changes, account lifecycle events.
- Device/session visibility foundation for users.
- Account deletion request flow (request-only, no hard delete).

## Core Security Decisions

- Guest is an unauthenticated state, not a database role.
- Privileged access uses least-privilege permissions with fixed core roles.
- `admin` cannot create or elevate `admin`/`super_admin` accounts.
- `super_admin` is required for internal privileged account provisioning.
- Protected user features (`client requests`, `protected downloads`) require verified email.
- Deactivated/suspended/deleted accounts are blocked from normal auth.
- Internal panel access requires active + verified + internal staff + admin permission.
- MFA is schema/service-ready (`mfa_methods`, `mfa_required_at`) without forcing a final MFA UX choice yet.

## Permission Convention

- Format: `resource.action` or `resource.scope.action`.
- Examples:
  - `profile.view`
  - `users.assignRoles`
  - `staff.create`
  - `security.sessions.viewOwn`
  - `downloads.protected.access`
  - `requests.create`

## Auth-Security Data Foundations

- `users` extended with status/internal/security lifecycle columns.
- `user_oauth_accounts` for provider identity linkage.
- `failed_login_attempts` for inspectable failed login tracking.
- `user_devices` + `user_session_histories` for user-visible security history.
- `mfa_methods` for MFA-ready architecture.
- `account_deletion_requests` extended with internal review notes.
- `audit_logs` refined with `event` + `created_at` alongside existing structure.

## Route/Middleware Baseline

- Public auth routes remain Breeze-based.
- Protected user area: `auth + active + session.track`.
- Protected feature routes: `verified.feature` + permission checks.
- Internal admin routes: `auth + active + admin.panel + internal.mfa`.
- SuperAdmin-only routes use `superadmin` middleware.

## Demo Seeder (Local/Staging Only)

- `LocalDevelopmentSeeder` provides sample users/roles for QA.
- Demo password: `ChangeMe123!Secure`.
- Enable via `.env`:
  - `USNSOFT_SEED_DEMO_USERS=true`
