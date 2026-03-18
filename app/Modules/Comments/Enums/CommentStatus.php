<?php

namespace App\Modules\Comments\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Hidden = 'hidden';
    case Spam = 'spam';
    case Flagged = 'flagged';

    public function isPubliclyVisible(): bool
    {
        return $this === self::Approved;
    }

    public function requiresHiddenContentPermission(): bool
    {
        return in_array($this, [self::Hidden, self::Spam, self::Flagged], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
