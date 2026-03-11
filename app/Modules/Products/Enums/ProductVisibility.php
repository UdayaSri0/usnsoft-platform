<?php

namespace App\Modules\Products\Enums;

enum ProductVisibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';

    public function isPubliclyListable(): bool
    {
        return $this === self::Public;
    }

    public function isPubliclyResolvable(): bool
    {
        return in_array($this, [self::Public, self::Unlisted], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
