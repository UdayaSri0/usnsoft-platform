<?php

namespace App\Modules\ClientRequests\Notifications;

use App\Modules\ClientRequests\Models\ProjectRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StaffProjectRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProjectRequest $projectRequest,
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
            'type' => 'project_request_submitted',
            'project_request_uuid' => $this->projectRequest->uuid,
            'project_title' => $this->projectRequest->project_title,
            'requester_name' => $this->projectRequest->requester_name,
            'company_name' => $this->projectRequest->company_name,
            'contact_email' => $this->projectRequest->contact_email,
            'submitted_at' => optional($this->projectRequest->submitted_at)->toIso8601String(),
        ];
    }
}
