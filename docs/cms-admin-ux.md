# CMS Admin UX

## Current Admin CMS Surfaces
- `/admin/cms/pages`
- `/admin/cms/pages/create`
- `/admin/cms/pages/{page}`
- `/admin/cms/approvals`
- `/admin/cms/reusable-blocks`
- `/admin/cms/block-definitions`

## Page Editor Structure
Page create/edit form is grouped into:
- Basics (title, slug/path, system flags)
- SEO snapshot
- Blocks and composition

Composition UX features:
- add/remove block cards
- block definition selector
- reusable block selection
- ordering and region assignment
- internal labels for editor clarity
- structured block JSON payload input per block

## Approval And Publishing UX
- Submit for review from page edit
- Approval queue with status and action entry points
- SuperAdmin-only approve/publish/schedule/archive actions
- Preview link generation with expiring token support

## Safety UX Notes
- UI clearly indicates safe structured blocks
- Block payloads are validated/sanitized server-side regardless of UI input
- Restricted block modes are enforced by permission checks in workflow service

## Public/Auth GUI Improvements Included
- Refined public layout shell (`layouts/public.blade.php`)
- Polished auth pages (login/register/reset/verify)
- Improved account dashboard/session/device pages
- Updated shared Tailwind design tokens and component styles in `resources/css/app.css`

## Future Admin UX Enhancements
1. Replace JSON textarea editing with schema-driven dynamic form controls per block type.
2. Add drag-and-drop reordering for blocks.
3. Add visual diff for review queue between versions.
4. Add dashboard widgets for pending approvals and scheduled publishes.
5. Integrate Filament resources/pages once Filament package is introduced in this repo.
