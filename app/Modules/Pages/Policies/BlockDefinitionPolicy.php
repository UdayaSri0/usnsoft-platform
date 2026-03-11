<?php

namespace App\Modules\Pages\Policies;

use App\Enums\CoreRole;
use App\Models\User;
use App\Modules\Pages\Enums\CmsPermission;
use App\Modules\Pages\Models\BlockDefinition;

class BlockDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(CmsPermission::BlocksViewDefinitions->value);
    }

    public function view(User $user, BlockDefinition $blockDefinition): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(CoreRole::SuperAdmin)
            && $user->hasPermission(CmsPermission::BlocksManageDefinitions->value);
    }

    public function update(User $user, BlockDefinition $blockDefinition): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, BlockDefinition $blockDefinition): bool
    {
        return false;
    }
}
