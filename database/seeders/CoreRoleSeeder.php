<?php

namespace Database\Seeders;

use App\Enums\CoreRole;
use App\Modules\IdentityAccess\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoreRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CoreRole::cases() as $coreRole) {
            Role::query()->updateOrCreate(
                ['name' => $coreRole->value],
                [
                    'display_name' => Str::headline(str_replace('_', ' ', $coreRole->value)),
                    'description' => 'Core system role',
                    'is_core' => true,
                    'is_internal' => $coreRole->isInternal(),
                ],
            );
        }
    }
}
