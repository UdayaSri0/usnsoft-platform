<?php

namespace App\Modules\IdentityAccess\Services\Admin;

use App\Enums\AccountStatus;
use App\Enums\SecurityEventType;
use App\Models\User;
use App\Modules\AuditSecurity\Services\SecurityEventService;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Services\AccountLifecycleService;
use App\Modules\IdentityAccess\Services\RoleAssignmentService;
use App\Services\Audit\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AccountManagementService
{
    public function __construct(
        private readonly AccountLifecycleService $accountLifecycleService,
        private readonly AuditLogService $auditLogService,
        private readonly DatabaseManager $database,
        private readonly AccountRoleScopeService $roleScopeService,
        private readonly RoleAssignmentService $roleAssignmentService,
        private readonly SecurityEventService $securityEventService,
    ) {}

    /**
     * @param  array{name: string, email: string, phone?: string|null, password: string}  $attributes
     */
    public function create(User $actor, array $attributes, Role $role): User
    {
        $this->roleScopeService->assertCanCreateWithRole($actor, $role);

        return $this->database->transaction(function () use ($actor, $attributes, $role): User {
            $user = User::query()->create([
                'name' => trim($attributes['name']),
                'email' => mb_strtolower($attributes['email']),
                'phone' => $this->nullableString($attributes['phone'] ?? null),
                'password' => Hash::make($attributes['password']),
                'status' => AccountStatus::Active,
                'is_internal' => $role->is_internal,
                'mfa_required_at' => $role->is_internal ? CarbonImmutable::now() : null,
            ]);

            $user->assignRole($role, $actor->getKey());

            $this->securityEventService->record(SecurityEventType::AccountCreated, $user, 'info', [
                'context' => 'managed_account_created',
                'created_by' => $actor->getKey(),
                'role' => $role->name,
            ]);

            $this->auditLogService->record(
                eventType: 'account.created.managed',
                action: 'create_managed_account',
                actor: $actor,
                auditable: $user,
                newValues: [
                    'email' => $user->email,
                    'role' => $role->name,
                    'status' => AccountStatus::Active->value,
                    'is_internal' => $role->is_internal,
                ],
                metadata: ['target_user_id' => $user->getKey()],
            );

            return $user->fresh(['roles']);
        });
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $attributes
     */
    public function update(User $actor, User $target, array $attributes, ?Role $role = null): User
    {
        if (! $this->roleScopeService->canManageTarget($actor, $target)) {
            throw new AuthorizationException('You are not allowed to manage this account.');
        }

        if ($role && ! $this->roleScopeService->canAssignRole($actor, $target, $role)) {
            throw new AuthorizationException('You are not allowed to assign that role.');
        }

        return $this->database->transaction(function () use ($actor, $attributes, $role, $target): User {
            $oldValues = [
                'name' => $target->name,
                'email' => $target->email,
                'phone' => $target->phone,
            ];

            $target->forceFill([
                'name' => trim($attributes['name']),
                'email' => mb_strtolower($attributes['email']),
                'phone' => $this->nullableString($attributes['phone'] ?? null),
            ])->save();

            if ($role) {
                $target->loadMissing('roles');

                foreach ($target->roles as $assignedRole) {
                    if ($assignedRole->is($role)) {
                        continue;
                    }

                    $this->roleAssignmentService->remove($actor, $target, $assignedRole);
                }

                $target->refresh()->load('roles');

                if (! $target->hasRole($role->name)) {
                    $this->roleAssignmentService->assign($actor, $target, $role);
                }
            }

            $this->auditLogService->record(
                eventType: 'account.updated',
                action: 'update_managed_account',
                actor: $actor,
                auditable: $target,
                oldValues: $oldValues,
                newValues: [
                    'name' => $target->name,
                    'email' => $target->email,
                    'phone' => $target->phone,
                ],
            );

            return $target->fresh(['roles']);
        });
    }

    public function deactivate(User $actor, User $target, ?string $reason = null): void
    {
        if ($actor->is($target)) {
            throw new AuthorizationException('Use your own account settings for self-service changes.');
        }

        $this->accountLifecycleService->deactivate($actor, $target, $this->nullableString($reason));
    }

    public function reactivate(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw new AuthorizationException('Use your own account settings for self-service changes.');
        }

        $this->accountLifecycleService->reactivate($actor, $target);
    }

    public function sendPasswordResetLink(User $actor, User $target): string
    {
        $status = Password::broker()->sendResetLink([
            'email' => $target->email,
        ]);

        $this->securityEventService->record(SecurityEventType::PasswordResetRequested, $target, 'info', [
            'actor_id' => $actor->getKey(),
            'initiated_by_staff' => true,
            'outcome' => $status,
        ]);

        $this->auditLogService->record(
            eventType: 'password.reset.requested.by_staff',
            action: 'initiate_password_reset_for_account',
            actor: $actor,
            auditable: $target,
            metadata: ['outcome' => $status],
        );

        return $status;
    }

    private function nullableString(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value !== '' ? $value : null;
    }
}
