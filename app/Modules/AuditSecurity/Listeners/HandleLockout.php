<?php

namespace App\Modules\AuditSecurity\Listeners;

use App\Modules\AuditSecurity\Services\LoginSecurityService;
use Illuminate\Auth\Events\Lockout;

class HandleLockout
{
    public function __construct(
        private readonly LoginSecurityService $loginSecurityService,
    ) {}

    public function handle(Lockout $event): void
    {
        $email = (string) $event->request->input('email', '');

        $this->loginSecurityService->recordLockout($email);
    }
}
