<?php

namespace App\Providers;

use App\Modules\Notifications\Events\BusinessEventDispatched;
use App\Modules\Notifications\Listeners\QueueBusinessEventNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        BusinessEventDispatched::class => [
            QueueBusinessEventNotification::class,
        ],
    ];
}
