# Workflow Baseline (Stage 0)

## Content Workflow States

`draft -> in_review -> approved -> scheduled -> published -> archived`

Defined by `App\Enums\ContentWorkflowState` with guarded transitions.

## Approval Workflow States

`draft -> pending_review -> (approved | rejected | changes_requested | cancelled)`

Defined by `App\Enums\ApprovalState`.

## Baseline Approval Rules

- Content contributors submit draft content into `pending_review`.
- Reviewers/superadmins approve/reject/request changes.
- Approval actions are auditable.
- Status transitions are recorded in `status_histories`.
- Schedule/publish orchestration exists as service-level baseline and will be connected to CMS UI in the next stage.

## Publishing Baseline

`PublishingService` validates transitions and records `status_histories` + audit entries.

## Preview and Scheduling

Preview and scheduled publish/unpublish are mandatory requirements and are preserved in the architecture, but full UI/cron orchestration is deferred to a follow-up stage.
