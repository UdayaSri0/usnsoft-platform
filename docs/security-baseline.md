# Security Baseline (Stage 0)

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

## Stage 0 Security Gaps (Planned)

- MFA enforcement for all internal staff (policy + middleware + UX)
- Login throttling/suspicious login automation and device history UI
- CAPTCHA/anti-spam for public forms
- Security headers hardening middleware/profile
