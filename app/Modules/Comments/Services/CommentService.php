<?php

namespace App\Modules\Comments\Services;

use App\Models\User;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Comments\Notifications\StaffPendingCommentNotification;
use App\Services\Audit\AuditLogService;
use App\Services\Notifications\OperationalNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly OperationalNotificationService $notificationService,
    ) {}

    /**
     * @param  array{body: string}  $payload
     */
    public function submit(Model $commentable, User $actor, array $payload): Comment
    {
        return $this->database->transaction(function () use ($actor, $commentable, $payload): Comment {
            $comment = Comment::query()->create([
                'commentable_type' => $commentable->getMorphClass(),
                'commentable_id' => $commentable->getKey(),
                'user_id' => $actor->getKey(),
                'body' => $this->sanitizeBody((string) $payload['body']),
                'status' => CommentStatus::Pending,
                'submitted_at' => CarbonImmutable::now(),
            ]);

            $comment->loadMissing(['user', 'commentable']);

            $this->auditLogService->record(
                eventType: 'comments.submitted',
                action: 'submit_comment',
                actor: $actor,
                auditable: $comment,
                metadata: [
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                ],
            );

            $this->notificationService->notifyUsersWithPermission(
                'comments.moderate',
                new StaffPendingCommentNotification($comment),
            );

            $this->notificationService->dispatchBusinessEvent('comments.submitted', [
                'comment_id' => $comment->getKey(),
                'commentable_type' => $comment->commentable_type,
                'commentable_id' => $comment->commentable_id,
            ]);

            return $comment;
        });
    }

    public function moderate(Comment $comment, CommentStatus $status, User $actor, ?string $reason = null): Comment
    {
        return $this->database->transaction(function () use ($actor, $comment, $reason, $status): Comment {
            $oldValues = [
                'status' => $comment->status?->value,
                'moderation_reason' => $comment->moderation_reason,
            ];

            $comment->forceFill([
                'status' => $status,
                'moderated_at' => CarbonImmutable::now(),
                'moderated_by' => $actor->getKey(),
                'moderation_reason' => $reason,
                'approved_at' => $status->isPubliclyVisible() ? CarbonImmutable::now() : null,
            ])->save();

            $this->auditLogService->record(
                eventType: 'comments.moderated',
                action: 'moderate_comment',
                actor: $actor,
                auditable: $comment,
                oldValues: $oldValues,
                newValues: [
                    'status' => $status->value,
                    'moderation_reason' => $reason,
                ],
                metadata: [
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                ],
            );

            $this->notificationService->dispatchBusinessEvent('comments.moderated', [
                'comment_id' => $comment->getKey(),
                'status' => $status->value,
                'commentable_type' => $comment->commentable_type,
                'commentable_id' => $comment->commentable_id,
            ]);

            return $comment->refresh();
        });
    }

    private function sanitizeBody(string $body): string
    {
        return trim(strip_tags($body));
    }
}
