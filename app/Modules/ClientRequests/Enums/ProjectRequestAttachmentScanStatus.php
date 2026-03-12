<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestAttachmentScanStatus: string
{
    case Pending = 'pending';
    case Clean = 'clean';
    case Flagged = 'flagged';
    case Failed = 'failed';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Clean => 'Clean',
            self::Flagged => 'Flagged',
            self::Failed => 'Failed',
            self::Unknown => 'Unknown',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
