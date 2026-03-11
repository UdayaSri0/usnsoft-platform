<?php

namespace App\Enums;

enum ContentWorkflowState: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * @return list<self>
     */
    public function nextStates(): array
    {
        return match ($this) {
            self::Draft => [self::InReview, self::Archived],
            self::InReview => [self::Draft, self::Approved, self::Archived],
            self::Approved => [self::Scheduled, self::Published, self::Archived],
            self::Scheduled => [self::Published, self::Archived],
            self::Published => [self::Archived],
            self::Archived => [self::Draft],
        };
    }

    public function canTransitionTo(self $nextState): bool
    {
        return in_array($nextState, $this->nextStates(), true);
    }

    public function isPubliclyVisibleState(): bool
    {
        return in_array($this, [self::Scheduled, self::Published], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
