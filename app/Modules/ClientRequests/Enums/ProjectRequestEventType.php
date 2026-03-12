<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestEventType: string
{
    case Quote = 'quote';
    case MeetingSuggestion = 'meeting_suggestion';
    case TimelineNote = 'timeline_note';
    case SystemUpdate = 'system_update';

    public function label(): string
    {
        return match ($this) {
            self::Quote => 'Quote',
            self::MeetingSuggestion => 'Meeting Suggestion',
            self::TimelineNote => 'Timeline Note',
            self::SystemUpdate => 'System Update',
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
