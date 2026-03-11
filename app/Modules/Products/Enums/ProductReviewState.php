<?php

namespace App\Modules\Products\Enums;

enum ProductReviewState: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Hidden = 'hidden';
    case Spam = 'spam';

    public function isPubliclyVisible(): bool
    {
        return $this === self::Approved;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
