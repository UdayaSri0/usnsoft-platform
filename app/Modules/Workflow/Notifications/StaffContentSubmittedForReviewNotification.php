<?php

namespace App\Modules\Workflow\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class StaffContentSubmittedForReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Model $content,
        private readonly string $contentType,
        private readonly string $title,
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
            'type' => 'content_submitted_for_review',
            'content_type' => $this->contentType,
            'content_id' => $this->content->getKey(),
            'content_title' => $this->title,
            'submitted_at' => optional($this->content->getAttribute('submitted_at'))->toIso8601String(),
        ];
    }
}
