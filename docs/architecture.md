# USNsoft Architecture (Stage 0)

## Purpose

This project is a single Laravel 12 codebase that hosts:

- Public website pages
- Product showcase and protected downloads
- Services and client request funnels
- Careers/blog/company profile content
- Internal role-based admin CMS
- Approval-driven publishing workflows
- Future extensions (tickets, invoices, payments, client dashboards)

## Core Stack

- Laravel 12, PHP 8.3+
- PostgreSQL (primary production database)
- Redis (queue/cache/session support)
- Blade + Tailwind for public UI
- Livewire + Filament for internal CMS (to be added in subsequent stages)
- Docker-first local and VPS deployment

## Single Codebase Strategy

All domains are organized under `app/Modules` with consistent internal subfolders (`Models`, `Policies`, `Requests`, `Services`, `Actions`, `Events`, `Listeners`, `Jobs`, `Enums`).

Cross-cutting abstractions are in:

- `app/Contracts`
- `app/Models/Concerns`
- `app/Services`
- `app/Enums`

This preserves modular boundaries without splitting repositories.

## Baseline Architectural Decisions

- Role/permission access control is explicit and policy-enforced.
- SuperAdmin has global override through `Gate::before`.
- Internal privileged role assignment is service-guarded and audited.
- Approval and publish workflows use reusable enums + generic history tables.
- Media uses an attachment abstraction (`media_assets`, `media_attachments`) to support S3-compatible storage.
- All privileged actions are designed to emit audit logs.
- Soft deletes are enabled on business/content tables where data recovery matters.
- Unsafe runtime code execution via admin content is out-of-scope and explicitly prohibited.

## Stage 0 Scope

Implemented now:

- Foundational schema and domain scaffolding
- Core enums/contracts/services/traits
- Authorization and policy baseline
- Core roles/permissions seed strategy
- Initial tests for boundaries and audit baseline

Deferred to next stages:

- Filament resources and admin UI workflows
- Public CMS page builder blocks
- Anti-spam/captcha and suspicious-login automation
- MFA UX and device/session management UI
