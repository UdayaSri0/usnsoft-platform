<?php

namespace App\Modules\AuditSecurity\Listeners;

use App\Modules\AuditSecurity\Services\LoginSecurityService;
use Illuminate\Auth\Events\Logout;

class HandleLogout
{
    public function __construct(
        private readonly LoginSecurityService $loginSecurityService,
    ) {}

    public function handle(Logout $event): void
    {
        $sessionId = request()?->session()?->getId();

        if (! $event->user) {
            return;
        }

        $this->loginSecurityService->recordLogout($event->user, $sessionId);
    }
}
