<?php

namespace App\Enums;

enum ApprovalAction: string
{
    case Submit = 'submit';
    case Approve = 'approve';
    case Reject = 'reject';
    case Publish = 'publish';
    case Schedule = 'schedule';
    case Unschedule = 'unschedule';
    case Archive = 'archive';
    case RestoreToDraft = 'restore_to_draft';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
