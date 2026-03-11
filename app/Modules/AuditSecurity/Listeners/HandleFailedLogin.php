<?php

namespace App\Modules\AuditSecurity\Listeners;

use App\Models\User;
use App\Modules\AuditSecurity\Services\LoginSecurityService;
use Illuminate\Auth\Events\Failed;

class HandleFailedLogin
{
    public function __construct(
        private readonly LoginSecurityService $loginSecurityService,
    ) {}

    public function handle(Failed $event): void
    {
        $email = is_string($event->credentials['email'] ?? null) ? $event->credentials['email'] : '';
        $user = $event->user;

        if (! $user && $email !== '') {
            $user = User::query()->withTrashed()->where('email', mb_strtolower($email))->first();
        }

        $this->loginSecurityService->recordFailedLogin($user, $email);
    }
}
