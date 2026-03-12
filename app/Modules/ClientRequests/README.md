# ClientRequests Module

Verified-user project and inquiry intake with protected attachments, request status history, requester-visible versus internal comments, in-app notifications, and audited staff workflows.

## Key Parts

- `Models`: `ProjectRequest`, `ProjectRequestStatus`, `ProjectRequestComment`, `ProjectRequestAttachment`, `ProjectRequestEvent`
- `Services`: submission, workflow transitions, comment visibility handling, attachment storage/download, notification recipient resolution
- `Requests`: validated submission, status transition, comment, and custom-status forms
- `Policies`: requester ownership, staff visibility, protected attachment downloads, comment visibility changes
- `Notifications`: queue-friendly database notifications for submission, visible status changes, and requester-visible comments

## Integration Notes

- Uses the shared `status_histories` table for append-only request lifecycle tracking.
- Uses the shared audit log service for privileged actions and protected attachment access events.
- Uses the shared media asset model for private file metadata while serving attachments through controlled routes.
- Uses the existing custom Blade/Tailwind admin area. Filament is not installed in the current repository, so this module does not introduce a second admin system.
