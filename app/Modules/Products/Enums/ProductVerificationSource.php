<?php

namespace App\Modules\Products\Enums;

enum ProductVerificationSource: string
{
    case Download = 'download';
    case PurchaseFuture = 'purchase_future';
    case AdminVerified = 'admin_verified';
    case Migration = 'migration';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
