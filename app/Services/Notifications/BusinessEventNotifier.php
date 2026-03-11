<?php

namespace App\Services\Notifications;

use App\Modules\Notifications\Events\BusinessEventDispatched;

class BusinessEventNotifier
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventName, array $payload = []): void
    {
        event(new BusinessEventDispatched($eventName, $payload));
    }
}
