<?php

namespace App\Enums;

enum VisibilityState: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Internal = 'internal';
    case Hidden = 'hidden';

    public function isPublic(): bool
    {
        return $this === self::Public;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
