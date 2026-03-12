<?php

namespace App\Modules\ClientRequests\Services;

use App\Models\User;
use App\Modules\ClientRequests\Models\ProjectRequest;
use App\Modules\ClientRequests\Models\ProjectRequestComment;
use App\Modules\ClientRequests\Models\ProjectRequestStatus;
use App\Modules\ClientRequests\Notifications\RequesterProjectRequestCommentAddedNotification;
use App\Modules\ClientRequests\Notifications\RequesterProjectRequestStatusChangedNotification;
use App\Modules\ClientRequests\Notifications\StaffProjectRequestSubmittedNotification;
use App\Services\Notifications\BusinessEventNotifier;
use Illuminate\Support\Facades\Notification;

class ProjectRequestNotificationService
{
    public function __construct(
        private readonly BusinessEventNotifier $businessEventNotifier,
        private readonly ProjectRequestNotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchBusinessEvent(string $eventName, array $payload = []): void
    {
        $this->businessEventNotifier->dispatch($eventName, $payload);
    }

    public function notifySubmitted(ProjectRequest $projectRequest): void
    {
        $recipients = $this->recipientResolver->submissionRecipients();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new StaffProjectRequestSubmittedNotification($projectRequest));
        }

        $this->dispatchBusinessEvent('client_requests.submitted', [
            'project_request_id' => $projectRequest->getKey(),
            'project_request_uuid' => $projectRequest->uuid,
        ]);
    }

    public function notifyRequesterOfStatusChange(ProjectRequest $projectRequest, ProjectRequestStatus $status, ?User $actor = null, ?string $note = null): void
    {
        $requester = $projectRequest->requester;

        if (! $requester || ($actor && $requester->is($actor))) {
            return;
        }

        $requester->notify(new RequesterProjectRequestStatusChangedNotification($projectRequest, $status, $note));
    }

    public function notifyRequesterOfVisibleComment(ProjectRequestComment $comment, ?User $actor = null): void
    {
        $requester = $comment->projectRequest?->requester;

        if (! $requester || ($actor && $requester->is($actor))) {
            return;
        }

        $requester->notify(new RequesterProjectRequestCommentAddedNotification($comment));
    }
}
