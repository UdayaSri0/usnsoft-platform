<?php

namespace App\Services\Seo;

class SeoMetadataService
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, string>
     */
    public function normalize(array $metadata): array
    {
        $defaults = [
            'title' => '',
            'description' => '',
            'canonical_url' => '',
            'robots' => 'index,follow',
            'og_image' => '',
        ];

        $normalized = array_merge($defaults, $metadata);

        return array_map(
            static fn (mixed $value): string => is_scalar($value) ? trim((string) $value) : '',
            $normalized,
        );
    }
}
