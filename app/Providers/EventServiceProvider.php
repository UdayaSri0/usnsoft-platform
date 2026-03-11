<?php

namespace App\Providers;

use App\Modules\AuditSecurity\Listeners\HandleFailedLogin;
use App\Modules\AuditSecurity\Listeners\HandleLockout;
use App\Modules\AuditSecurity\Listeners\HandleLogout;
use App\Modules\IdentityAccess\Listeners\HandlePasswordReset;
use App\Modules\IdentityAccess\Listeners\HandlePasswordResetLinkSent;
use App\Modules\IdentityAccess\Listeners\HandleRegisteredUser;
use App\Modules\IdentityAccess\Listeners\HandleVerifiedUser;
use App\Modules\Notifications\Events\BusinessEventDispatched;
use App\Modules\Notifications\Listeners\QueueBusinessEventNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
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
        Failed::class => [
            HandleFailedLogin::class,
        ],
        Lockout::class => [
            HandleLockout::class,
        ],
        Logout::class => [
            HandleLogout::class,
        ],
        Registered::class => [
            HandleRegisteredUser::class,
        ],
        Verified::class => [
            HandleVerifiedUser::class,
        ],
        PasswordReset::class => [
            HandlePasswordReset::class,
        ],
        PasswordResetLinkSent::class => [
            HandlePasswordResetLinkSent::class,
        ],
    ];
}
