<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestType: string
{
    case ProjectIdea = 'project_idea';
    case Inquiry = 'inquiry';
    case QuotationRequest = 'quotation_request';
    case MeetingRequest = 'meeting_request';
    case ImplementationRequest = 'implementation_request';

    public function label(): string
    {
        return match ($this) {
            self::ProjectIdea => 'Project Idea',
            self::Inquiry => 'Inquiry',
            self::QuotationRequest => 'Quotation Request',
            self::MeetingRequest => 'Meeting Request',
            self::ImplementationRequest => 'Implementation Request',
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
