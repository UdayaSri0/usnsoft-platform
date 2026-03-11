<?php

namespace App\Modules\Notifications\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendBusinessEventNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $eventName,
        public readonly array $payload = [],
    ) {}

    public function handle(): void
    {
        // Stage 0 baseline: queue execution path exists.
        // Channel-specific notification delivery is added per-module.
    }
}
