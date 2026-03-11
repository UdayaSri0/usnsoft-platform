<?php

namespace App\Models\Concerns;

trait HasSeoMetadata
{
    /**
     * @return array<string, mixed>
     */
    public function getSeoMetadata(): array
    {
        $metadata = $this->getAttribute('seo_meta');

        return is_array($metadata) ? $metadata : [];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function setSeoMetadata(array $metadata): void
    {
        $this->setAttribute('seo_meta', $metadata);
    }
}
