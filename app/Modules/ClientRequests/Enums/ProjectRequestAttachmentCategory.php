<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestAttachmentCategory: string
{
    case Attachment = 'attachment';
    case Screenshot = 'screenshot';
    case Pdf = 'pdf';
    case Diagram = 'diagram';
    case ScopeDocument = 'scope_document';
    case VoiceNote = 'voice_note';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Attachment => 'Attachment',
            self::Screenshot => 'Screenshot',
            self::Pdf => 'PDF',
            self::Diagram => 'Diagram',
            self::ScopeDocument => 'Scope Document',
            self::VoiceNote => 'Voice Note',
            self::Other => 'Other',
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
