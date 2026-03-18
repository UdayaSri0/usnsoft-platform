<?php

namespace App\Modules\Comments\Notifications;

use App\Modules\Comments\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StaffPendingCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Comment $comment,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment_pending_moderation',
            'comment_id' => $this->comment->getKey(),
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
            'target_label' => $this->comment->targetLabel(),
            'target_title' => $this->comment->targetTitle(),
            'author_name' => $this->comment->user?->name,
            'author_email' => $this->comment->user?->email,
            'submitted_at' => optional($this->comment->submitted_at)->toIso8601String(),
        ];
    }
}
