<?php

namespace App\Modules\Pages\Enums;

enum PageType: string
{
    case System = 'system';
    case Custom = 'custom';
    case Landing = 'landing';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
