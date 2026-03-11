<?php

namespace App\Modules\IdentityAccess\Enums;

enum AccountDeletionRequestStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
