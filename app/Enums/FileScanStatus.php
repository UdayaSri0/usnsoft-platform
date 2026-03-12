<?php

namespace App\Enums;

enum FileScanStatus: string
{
    case Pending = 'pending';
    case Clean = 'clean';
    case Flagged = 'flagged';
    case Failed = 'failed';
    case Unknown = 'unknown';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
