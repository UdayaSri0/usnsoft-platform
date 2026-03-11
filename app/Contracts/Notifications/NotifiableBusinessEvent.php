<?php

namespace App\Contracts\Notifications;

interface NotifiableBusinessEvent
{
    public function businessEventName(): string;

    /**
     * @return array<string, mixed>
     */
    public function businessEventPayload(): array;
}
