# Module Map (Stage 0)

All modules live in `app/Modules`.

## Active/Seeded Modules

- `IdentityAccess`: users, roles, permissions, policies, role assignment boundaries
- `SiteSettings`: tenant/site config and branding primitives
- `Media`: storage abstraction and ownership/attachment baseline
- `Workflow`: approval and publish-state history baseline
- `AuditSecurity`: structured audit + security event models
- `Notifications`: queued business-event notification baseline

## Reserved Modules (scaffolded for next stages)

- `Pages`: CMS pages + safe block-based composition
- `Products`: products, versions, protected downloads, review gating
- `Blog`: news/blog posts and editorial workflows
- `Services`: service catalog and solution offerings
- `Faq`: FAQs and grouped entries
- `Showcase`: testimonials/partners/team/timeline/achievements
- `ClientRequests`: leads, inquiries, project/request intake
- `Careers`: positions, applications, applicant workflow

## Shared Conventions

- Models: `app/Modules/<Module>/Models`
- Enums: `app/Enums` (global) and module-local enums when scope is module-only
- Policies: module policy classes, registered centrally
- Form requests: module request classes with explicit `authorize` + rules
- Services: orchestration for business rules (approval, audit, publishing, media)
- Actions: focused units wrapping one use-case (`execute` method)
- Jobs/Events/Listeners: asynchronous and integration boundaries
- Seeders: deterministic core identity and permission scaffolding
- Tests: feature tests for authorization boundaries and unit tests for enum/service logic
