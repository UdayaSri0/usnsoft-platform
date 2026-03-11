<?php

namespace App\Models\Concerns;

use App\Services\Notifications\BusinessEventNotifier;

trait DispatchesBusinessEvents
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchBusinessEvent(string $eventName, array $payload = []): void
    {
        app(BusinessEventNotifier::class)->dispatch($eventName, $payload);
    }
}
