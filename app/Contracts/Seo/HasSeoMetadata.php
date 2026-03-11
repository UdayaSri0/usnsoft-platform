<?php

namespace App\Contracts\Seo;

interface HasSeoMetadata
{
    /**
     * @return array<string, mixed>
     */
    public function getSeoMetadata(): array;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function setSeoMetadata(array $metadata): void;
}
