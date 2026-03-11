<?php

namespace App\Modules\Products\Enums;

enum ProductDownloadVisibility: string
{
    case Authenticated = 'authenticated';
    case Verified = 'verified';
    case Internal = 'internal';
    case Entitled = 'entitled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
