<?php

namespace App\Modules\ClientRequests\Notifications;

use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequesterProjectRequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProjectRequest $projectRequest,
        private readonly ProjectRequestStatus $status,
        private readonly ?string $note = null,
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
            'type' => 'project_request_status_changed',
            'project_request_uuid' => $this->projectRequest->uuid,
            'project_title' => $this->projectRequest->project_title,
            'status_name' => $this->status->name,
            'status_code' => $this->status->code,
            'note' => $this->note,
        ];
    }
}
