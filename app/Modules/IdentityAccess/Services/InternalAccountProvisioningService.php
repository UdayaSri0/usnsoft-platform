<?php

namespace App\Modules\IdentityAccess\Services;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;

class InternalAccountProvisioningService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $attributes
     */
    public function createInternalAccount(User $actor, array $attributes, Role $role): User
    {
        if (! $actor->hasRole(CoreRole::SuperAdmin)) {
            throw new AuthorizationException('Only SuperAdmin can create internal accounts.');
        }

        if (! $role->is_internal) {
            throw new AuthorizationException('Internal account provisioning requires an internal role.');
        }

        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => mb_strtolower($attributes['email']),
            'password' => Hash::make($attributes['password']),
            'status' => AccountStatus::Active,
            'is_internal' => true,
            'mfa_required_at' => CarbonImmutable::now(),
        ]);

        $user->assignRole($role, $actor->getKey());

        $this->securityEventService->record(SecurityEventType::AccountCreated, $user, 'info', [
            'context' => 'internal_account_provisioned',
            'created_by' => $actor->getKey(),
            'role' => $role->name,
        ]);

        $this->auditLogService->record(
            eventType: 'account.created.internal',
            action: 'create_internal_account',
            actor: $actor,
            auditable: $user,
            newValues: [
                'role' => $role->name,
                'email' => $user->email,
            ],
            metadata: ['created_user_id' => $user->getKey()],
        );

        return $user;
    }
}
