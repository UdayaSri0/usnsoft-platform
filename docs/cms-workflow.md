# CMS Workflow

## States
Content workflow uses `App\Enums\ContentWorkflowState`:
- `draft`
- `in_review`
- `approved`
- `scheduled`
- `published`
- `archived`

Approval workflow uses `App\Enums\ApprovalState`:
- `draft`
- `pending_review`
- `approved`
- `rejected`
- `changes_requested`
- `cancelled`

## Allowed Transitions
Workflow transitions:
- `draft -> in_review`
- `in_review -> approved`
- `in_review -> draft` (reject/changes)
- `approved -> scheduled`
- `approved -> published`
- `scheduled -> published`
- `published -> archived`
- `scheduled -> archived`

Approval transitions:
- `draft -> pending_review`
- `pending_review -> approved|rejected|changes_requested|cancelled`

## Guard Rails
- Only draft versions can be edited.
- Publish requires:
  - state `approved` or `scheduled`
  - approval state `approved`
  - preview confirmation timestamp
  - authorized actor (route/policy checks)
- Editing a published page branches a new draft.
- Public site never reads draft versions.

## Roles And Privileges
- Admin/editor roles can edit drafts and submit for review if permitted.
- SuperAdmin approval is required before publishing.
- SuperAdmin-only actions are enforced by permission + role checks.

## Preview
- Internal user with preview permission can open preview directly.
- External/unauthenticated preview requires a valid token.
- Preview token is hashed, expiring, and auditable.
- Preview responses set `Cache-Control: no-store, private` and force robots noindex/nofollow.

## Scheduling
- `CmsWorkflowService::schedulePublish()` stores publish time (and optional unpublish time).
- `cms:process-scheduled-pages` command executes transitions.
- Scheduler runs command every minute.

## Audit And History
Recorded events include:
- draft creation/update
- submit/approve/reject/schedule/publish/archive
- preview generated/accessed

Persistence layers:
- `status_histories`
- `approval_requests`
- `approval_records`
- `audit_logs`

## Operational Commands
Run scheduled content transitions manually:

```bash
php artisan cms:process-scheduled-pages
```

Run queue worker if notifications/jobs are involved:

```bash
php artisan queue:work
```
