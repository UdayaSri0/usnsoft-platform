<?php

namespace App\Modules\ClientRequests\Notifications;

use App\Modules\ClientRequests\Models\ProjectRequestComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequesterProjectRequestCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProjectRequestComment $comment,
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
        $projectRequest = $this->comment->projectRequest;

        return [
            'type' => 'project_request_public_comment_added',
            'project_request_uuid' => $projectRequest->uuid,
            'project_title' => $projectRequest->project_title,
            'comment_excerpt' => mb_substr($this->comment->body, 0, 160),
        ];
    }
}
