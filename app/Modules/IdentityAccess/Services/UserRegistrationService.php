<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    public function registerPublicUser(string $name, string $email, string $password): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => mb_strtolower($email),
            'password' => Hash::make($password),
            'status' => AccountStatus::Active,
            'is_internal' => false,
        ]);

        $defaultRole = Role::query()->firstOrCreate(
            ['name' => CoreRole::User->value],
            [
                'display_name' => 'User',
                'description' => 'Default self-registered account role',
                'is_core' => true,
                'is_internal' => false,
            ],
        );

        $user->assignRole($defaultRole);

        $this->securityEventService->record(SecurityEventType::AccountCreated, $user, 'info', [
            'context' => 'public_registration',
        ]);

        $this->auditLogService->record(
            eventType: SecurityEventType::AccountCreated->value,
            action: 'register_public_user',
            actor: $user,
            auditable: $user,
            newValues: ['role' => CoreRole::User->value],
        );

        return $user;
    }
}
