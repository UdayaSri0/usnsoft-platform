# Security Baseline

## Identity and Access

- Email verification enabled at model level (`MustVerifyEmail`).
- Roles + permissions implemented with explicit policy checks.
- SuperAdmin override is centralized and auditable.
- Internal role assignment boundary is enforced by `RoleAssignmentService`.

## Logging and Auditability

- Structured `audit_logs` table for privileged and critical operations.
- `security_events` table for authentication/security telemetry.
- Audit retention model is append-only baseline (no soft delete on audit logs).

## Data Safety

- Soft deletes on user/business/content foundations where recovery is important.
- No dynamic code execution surfaces (PHP/Blade/JS) in content models.
- Media abstraction supports visibility controls (`public`, `protected`, `internal`, `hidden`).

## Platform Baseline Controls

- CSRF protection and session support via Laravel defaults.
- Queue-ready business notification architecture.
- Storage abstraction supports local development and S3-compatible production.

## Phase 1 Hardening In Place

- Staff MFA architecture with TOTP setup, recovery codes, and enforced challenge/setup routing for internal accounts.
- Failed login, suspicious login, session timeout, and protected-file access signals feed into `security_events`.
- Audit/security visibility is split by permission area instead of one broad log gate.
- Public-form anti-spam abstraction supports a null local provider and a Turnstile production path.
- Security headers are applied through middleware with environment-aware HSTS handling.

## Remaining Future Work

- CSP rollout from report-only to enforced once front-end compatibility is fully audited.
- archival strategy for very old audit/security records
- optional second-step approvals for more destructive operations if future workflows need them
