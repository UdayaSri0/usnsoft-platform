<?php

namespace App\Modules\Pages\Enums;

enum BlockEditorMode: string
{
    case Basic = 'basic';
    case Advanced = 'advanced';
    case SuperAdminOnly = 'superadmin_only';

    public function allowsNonSuperAdmin(): bool
    {
        return in_array($this, [self::Basic, self::Advanced], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
