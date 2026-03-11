<?php

namespace App\Modules\SiteSettings\Services;

use App\Models\User;
use App\Modules\SiteSettings\Models\SiteSetting;

class SiteSettingService
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        return SiteSetting::query()->where('key', $key)->value('value');
    }

    /**
     * @param  array<string, mixed>|null  $value
     */
    public function set(string $group, string $key, ?array $value, ?User $updatedBy = null): SiteSetting
    {
        return SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $value,
                'updated_by' => $updatedBy?->getKey(),
                'value_type' => 'json',
            ],
        );
    }
}
