# Domain Model (Stage 0 Foundation)

## Core Identity

- `users`
  - Auth identity, verification status, MFA marker, login tracking, deletion request marker
  - Soft deletes enabled
- `roles`
  - Core and custom role definitions (`is_core`, `is_internal`)
  - Soft deletes enabled
- `permissions`
  - Granular capability records grouped by module
  - Soft deletes enabled
- `role_user`
  - User-role assignment pivot with `assigned_by` and timestamps
- `permission_role`
  - Role-permission mapping pivot

## Audit and Security

- `audit_logs`
  - Immutable-style structured action logs (`event_type`, `action`, actor, auditable morph, value diffs, metadata, context)
- `security_events`
  - Security signal storage (login failures, suspicious activity, permission denials, etc.)

## Site Settings

- `site_settings`
  - Key/value settings storage for platform config and branding
  - Public/private flag support
  - Soft deletes enabled

## Media Abstraction

- `media_assets`
  - Storage-agnostic file record with disk/path/visibility/checksum/metadata
  - Soft deletes enabled
- `media_attachments`
  - Morph attachment mapping to any business/content entity with collection support
  - Soft deletes enabled

## Workflow Foundation

- `approval_requests`
  - Generic approval trail per approvable entity
  - Supports requester/reviewer, comments, metadata, states
  - Soft deletes enabled
- `status_histories`
  - Generic workflow/status transition timeline for publishable entities

## Relational Highlights

- Users ↔ Roles: many-to-many (`role_user`)
- Roles ↔ Permissions: many-to-many (`permission_role`)
- Any entity ↔ Media: morph one-to-many through `media_attachments`
- Any entity ↔ Approval: morph one-to-many (`approval_requests`)
- Any entity ↔ Status history: morph one-to-many (`status_histories`)
- Audit links to actor user and optional auditable morph target
