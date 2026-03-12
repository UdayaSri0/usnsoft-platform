<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestSystemStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case NeedMoreInfo = 'need_more_info';
    case Quoted = 'quoted';
    case Approved = 'approved';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::NeedMoreInfo => 'Need More Info',
            self::Quoted => 'Quoted',
            self::Approved => 'Approved',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeTone(): string
    {
        return match ($this) {
            self::Submitted, self::Quoted, self::InProgress => 'info',
            self::UnderReview, self::NeedMoreInfo => 'warning',
            self::Approved, self::Completed => 'success',
            self::Rejected => 'danger',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
