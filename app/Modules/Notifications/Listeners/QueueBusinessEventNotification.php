<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Events\BusinessEventDispatched;
use App\Modules\Notifications\Jobs\SendBusinessEventNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueBusinessEventNotification implements ShouldQueue
{
    public function handle(BusinessEventDispatched $event): void
    {
        SendBusinessEventNotificationJob::dispatch($event->eventName, $event->payload);
    }
}
