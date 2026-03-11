<?php

namespace App\Modules\Products\Enums;

enum ProductPricingMode: string
{
    case Free = 'free';
    case ContactSales = 'contact_sales';
    case OneTime = 'one_time';
    case Subscription = 'subscription';
    case Custom = 'custom';
    case OpenSource = 'open_source';
    case InternalOnly = 'internal_only';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
