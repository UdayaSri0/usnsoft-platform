<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use App\Modules\ClientRequests\Enums\ProjectRequestCommentVisibility;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestComment;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;

class ProjectRequestCommentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly ProjectRequestNotificationService $notificationService,
    ) {}

    public function add(ProjectRequest $projectRequest, User $author, string $body, ProjectRequestCommentVisibility $visibility): ProjectRequestComment
    {
        return $this->database->transaction(function () use ($author, $body, $projectRequest, $visibility): ProjectRequestComment {
            $comment = ProjectRequestComment::query()->create([
                'project_request_id' => $projectRequest->getKey(),
                'author_user_id' => $author->getKey(),
                'body' => $this->sanitizeBody($body),
                'visibility_type' => $visibility,
                'is_system_generated' => false,
            ]);

            $this->auditLogService->record(
                eventType: $visibility === ProjectRequestCommentVisibility::Internal
                    ? 'requests.comment.internal_created'
                    : 'requests.comment.requester_visible_created',
                action: 'create_project_request_comment',
                actor: $author,
                auditable: $comment,
                newValues: [
                    'visibility_type' => $comment->visibility_type->value,
                ],
                metadata: [
                    'project_request_id' => $projectRequest->getKey(),
                ],
            );

            $this->notificationService->dispatchBusinessEvent('client_requests.comment_added', [
                'project_request_id' => $projectRequest->getKey(),
                'comment_id' => $comment->getKey(),
                'visibility_type' => $comment->visibility_type->value,
            ]);

            if ($visibility === ProjectRequestCommentVisibility::RequesterVisible) {
                $this->notificationService->notifyRequesterOfVisibleComment($comment, $author);
            }

            return $comment->fresh(['author']);
        });
    }

    public function changeVisibility(ProjectRequestComment $comment, ProjectRequestCommentVisibility $visibility, User $actor): ProjectRequestComment
    {
        return $this->database->transaction(function () use ($actor, $comment, $visibility): ProjectRequestComment {
            $oldVisibility = $comment->visibility_type;

            if ($oldVisibility === $visibility) {
                return $comment;
            }

            $comment->forceFill([
                'visibility_type' => $visibility,
                'visibility_changed_by_user_id' => $actor->getKey(),
                'visibility_changed_at' => CarbonImmutable::now(),
            ])->save();

            $this->auditLogService->record(
                eventType: 'requests.comment.visibility_changed',
                action: 'change_project_request_comment_visibility',
                actor: $actor,
                auditable: $comment,
                oldValues: [
                    'visibility_type' => $oldVisibility?->value,
                ],
                newValues: [
                    'visibility_type' => $visibility->value,
                ],
                metadata: [
                    'project_request_id' => $comment->project_request_id,
                ],
            );

            $this->notificationService->dispatchBusinessEvent('client_requests.comment_visibility_changed', [
                'project_request_id' => $comment->project_request_id,
                'comment_id' => $comment->getKey(),
                'visibility_type' => $visibility->value,
            ]);

            if ($visibility === ProjectRequestCommentVisibility::RequesterVisible) {
                $this->notificationService->notifyRequesterOfVisibleComment($comment->fresh(['projectRequest.requester']), $actor);
            }

            return $comment->fresh(['author', 'visibilityChanger']);
        });
    }

    private function sanitizeBody(string $body): string
    {
        return trim(strip_tags($body));
    }
}
