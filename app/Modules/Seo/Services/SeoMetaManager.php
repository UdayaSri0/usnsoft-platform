<?php

namespace App\Modules\Seo\Services;

use App\Modules\Seo\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;

class SeoMetaManager
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function upsert(Model $model, array $attributes): SeoMeta
    {
        $payload = [
            'meta_title' => $this->truncateNullable($attributes['meta_title'] ?? null, 255),
            'meta_description' => $this->nullableString($attributes['meta_description'] ?? null),
            'canonical_url' => $this->nullableString($attributes['canonical_url'] ?? null),
            'og_title' => $this->truncateNullable($attributes['og_title'] ?? null, 255),
            'og_description' => $this->nullableString($attributes['og_description'] ?? null),
            'og_image_media_id' => $this->nullableString($attributes['og_image_media_id'] ?? null),
            'robots_index' => (bool) ($attributes['robots_index'] ?? true),
            'robots_follow' => (bool) ($attributes['robots_follow'] ?? true),
            'schema_type' => $this->truncateNullable($attributes['schema_type'] ?? null, 80),
            'extra_json' => is_array($attributes['extra_json'] ?? null) ? $attributes['extra_json'] : null,
        ];

        return $model->seoMeta()->updateOrCreate([], $payload);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function truncateNullable(mixed $value, int $max): ?string
    {
        $normalized = $this->nullableString($value);

        if ($normalized === null) {
            return null;
        }

        return mb_substr($normalized, 0, $max);
    }
}
