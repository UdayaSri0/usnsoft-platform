<?php

namespace App\Modules\Careers\Enums;

enum JobApplicationFileType: string
{
    case Cv = 'cv';
    case CoverLetter = 'cover_letter';
    case SupportingDocument = 'supporting_document';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
