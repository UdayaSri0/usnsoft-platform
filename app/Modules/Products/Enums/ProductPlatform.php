<?php

namespace App\Modules\Products\Enums;

enum ProductPlatform: string
{
    case Windows = 'windows';
    case MacOS = 'macos';
    case Linux = 'linux';
    case IOS = 'ios';
    case Android = 'android';
    case Web = 'web';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
