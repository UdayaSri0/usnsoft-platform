<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LocalDevelopmentSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'ChangeMe123!Secure';

    /**
     * @return array<int, array{name: string, email: string, role: CoreRole, verified: bool, status: AccountStatus}>
     */
    private function accounts(): array
    {
        return [
            ['name' => 'USNsoft Super Admin', 'email' => 'superadmin@usnsoft.test', 'role' => CoreRole::SuperAdmin, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Admin', 'email' => 'admin@usnsoft.test', 'role' => CoreRole::Admin, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Editor', 'email' => 'editor@usnsoft.test', 'role' => CoreRole::Editor, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Product Manager', 'email' => 'productmanager@usnsoft.test', 'role' => CoreRole::ProductManager, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Sales Manager', 'email' => 'salesmanager@usnsoft.test', 'role' => CoreRole::SalesManager, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Developer', 'email' => 'developer@usnsoft.test', 'role' => CoreRole::Developer, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Support', 'email' => 'support@usnsoft.test', 'role' => CoreRole::SupportOperations, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft User', 'email' => 'user@usnsoft.test', 'role' => CoreRole::User, 'verified' => true, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Unverified User', 'email' => 'unverified-user@usnsoft.test', 'role' => CoreRole::User, 'verified' => false, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Deactivated User', 'email' => 'deactivated-user@usnsoft.test', 'role' => CoreRole::User, 'verified' => true, 'status' => AccountStatus::Deactivated],
            ['name' => 'USNsoft Staff Unverified', 'email' => 'staff-unverified@usnsoft.test', 'role' => CoreRole::Editor, 'verified' => false, 'status' => AccountStatus::Active],
            ['name' => 'USNsoft Staff Suspended', 'email' => 'staff-suspended@usnsoft.test', 'role' => CoreRole::SupportOperations, 'verified' => true, 'status' => AccountStatus::Suspended],
        ];
    }

    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            if ($this->command) {
                $this->command->warn('LocalDevelopmentSeeder skipped outside local/staging environments.');
            }

            return;
        }

        $password = Hash::make(self::DEMO_PASSWORD);

        foreach ($this->accounts() as $account) {
            $role = Role::query()->where('name', $account['role']->value)->first();

            if (! $role) {
                continue;
            }

            $isInternal = $role->is_internal;
            $isDeactivated = $account['status'] === AccountStatus::Deactivated;
            $isSuspended = $account['status'] === AccountStatus::Suspended;

            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $password,
                    'email_verified_at' => $account['verified'] ? CarbonImmutable::now() : null,
                    'status' => $account['status'],
                    'is_internal' => $isInternal,
                    'deactivated_at' => $isDeactivated ? CarbonImmutable::now() : null,
                    'suspended_at' => $isSuspended ? CarbonImmutable::now() : null,
                    'mfa_required_at' => $isInternal ? CarbonImmutable::now() : null,
                ],
            );

            $user->roles()->sync([
                $role->getKey() => ['assigned_by' => $user->getKey()],
            ]);
        }

        if ($this->command) {
            $this->command->warn('Demo accounts seeded for local/staging only.');
            $this->command->warn('Demo password (change immediately): '.self::DEMO_PASSWORD);
        }
    }
}
