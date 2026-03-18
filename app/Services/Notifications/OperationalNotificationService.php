<?php

namespace App\Services\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OperationalNotificationService
{
    public function __construct(
        private readonly BusinessEventNotifier $businessEventNotifier,
        private readonly InternalNotificationRecipientResolver $recipientResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchBusinessEvent(string $eventName, array $payload = []): void
    {
        $this->businessEventNotifier->dispatch($eventName, $payload);
    }

    /**
     * @param  string|array<int, string>  $permissions
     */
    public function notifyUsersWithPermission(string|array $permissions, Notification $notification): void
    {
        $recipients = $this->recipientResolver->usersWithAnyPermission($permissions);

        if ($recipients->isEmpty()) {
            return;
        }

        NotificationFacade::send($recipients, $notification);
    }
}
