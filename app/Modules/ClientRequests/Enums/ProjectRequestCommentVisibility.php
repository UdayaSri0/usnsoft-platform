<?php

namespace App\Modules\ClientRequests\Enums;

enum ProjectRequestCommentVisibility: string
{
    case Internal = 'internal';
    case RequesterVisible = 'requester_visible';

    public function label(): string
    {
        return match ($this) {
            self::Internal => 'Internal',
            self::RequesterVisible => 'Requester Visible',
        };
    }

    public function isVisibleToRequester(): bool
    {
        return $this === self::RequesterVisible;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
