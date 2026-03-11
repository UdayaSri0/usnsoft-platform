# CMS Core Architecture

## Scope
Stage CMS core introduces a safe, versioned content system for the USNsoft single Laravel codebase.

Implemented concerns:
- Stable page identity (`pages`)
- Versioned editable content (`page_versions`)
- Structured blocks per version (`page_version_blocks`)
- Reusable block library (`reusable_blocks`)
- Block catalog (`block_definitions`)
- Version snapshot SEO metadata (`seo_snapshot_json`) with reusable polymorphic SEO table (`seo_meta`)
- Approval and transition history (`approval_records`, existing `approval_requests`, existing `status_histories`)
- Time-limited preview tokens (`preview_access_tokens`)

## Key Design Decisions
- Published content is immutable in-place. Editing a published page branches a new draft version.
- Public rendering always resolves `pages.current_published_version_id`.
- Block payloads are schema-driven and sanitized server-side.
- No runtime code injection is allowed from CMS input.
- Publish, schedule, and archive transitions are centralized in `CmsWorkflowService`.
- Preview is explicit and auditable.

## Main Runtime Flow
1. Admin/editor creates/edits a page draft.
2. Draft blocks are validated against the block definition schema.
3. Draft moves to `in_review` via submit.
4. SuperAdmin approves.
5. Preview is confirmed.
6. Version is published now or scheduled.
7. Public route resolves and renders only published version blocks.

## Core Services
- `App\Modules\Pages\Services\CmsWorkflowService`
  - Draft branching
  - State transitions
  - Scheduling and publish/archive orchestration
- `App\Modules\Pages\Services\BlockValidationService`
  - Config-driven schema validation
- `App\Modules\Pages\Services\BlockSanitizerService`
  - Rich text sanitization and URL allowlisting
- `App\Modules\Pages\Services\PageRenderService`
  - Published page resolution + render cache
- `App\Modules\Pages\Services\PreviewTokenService`
  - Preview token issue/verify + audit trail

## Public Rendering
- Route: `/{path?}` handled by `CmsPageController`
- Renderer loads published version and ordered blocks
- Each block uses an explicit Blade partial under `resources/views/components/blocks`
- Preview route (`/preview/pages/{version}`) supports either:
  - authorized internal user with preview permission, or
  - valid preview token

## Admin Surface
Current CMS admin surface is route/controller Blade driven under `/admin/cms/*`:
- Pages management
- Approval queue
- Reusable blocks
- Block definitions metadata

This preserves policy/middleware enforcement and avoids business logic inside view closures.

## Security Boundaries
- No raw HTML/code block type
- Rich text is sanitized server-side
- Video embeds are provider allowlisted
- Advanced and superadmin-only blocks are permission checked in workflow service
- Reusable blocks must be approved for non-superadmin use
- Preview access is time-limited and audited

## Scheduler Integration
- Command: `cms:process-scheduled-pages`
- Registered in `routes/console.php` every minute
- Handles scheduled publish and scheduled archive transitions
