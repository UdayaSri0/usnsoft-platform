# Module-Oriented App Structure

USNsoft uses a single Laravel codebase with module-oriented boundaries under `app/Modules`.

Each module keeps the same internal structure to support long-term maintainability:

- `Models`
- `Policies`
- `Requests`
- `Services`
- `Actions`
- `Events`
- `Listeners`
- `Jobs`
- `Enums`

Current Stage 0 modules:

- `IdentityAccess`
- `SiteSettings`
- `Pages`
- `Products`
- `Blog`
- `Services`
- `Faq`
- `Showcase`
- `ClientRequests`
- `Careers`
- `Media`
- `Workflow`
- `AuditSecurity`
- `Notifications`

Cross-cutting reusable contracts, traits, and service abstractions remain in:

- `app/Contracts`
- `app/Models/Concerns`
- `app/Services`
- `app/Enums`

This keeps one codebase while preserving domain ownership and policy boundaries.
