<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\IdentityAccess\Models\Role;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('USNSOFT_SUPERADMIN_EMAIL');
        $name = env('USNSOFT_SUPERADMIN_NAME');
        $password = env('USNSOFT_SUPERADMIN_PASSWORD');

        if (! $email || ! $name || ! $password) {
            if ($this->command) {
                $this->command->warn('SuperAdmin account was not auto-created (safe default).');
                $this->command->line('To bootstrap one account, set: USNSOFT_SUPERADMIN_EMAIL, USNSOFT_SUPERADMIN_NAME, USNSOFT_SUPERADMIN_PASSWORD');
                $this->command->line('Then run: php artisan db:seed --class=SuperAdminBootstrapSeeder');
            }

            return;
        }

        $superAdminRole = Role::query()->where('name', CoreRole::SuperAdmin->value)->first();

        if (! $superAdminRole) {
            if ($this->command) {
                $this->command->error('SuperAdmin role is missing. Run CoreRoleSeeder first.');
            }

            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => AccountStatus::Active,
                'is_internal' => true,
                'mfa_required_at' => CarbonImmutable::now(),
            ],
        );

        $user->assignRole($superAdminRole, $user->getKey());

        if ($this->command) {
            $this->command->info('SuperAdmin account bootstrapped successfully.');
        }
    }
}
