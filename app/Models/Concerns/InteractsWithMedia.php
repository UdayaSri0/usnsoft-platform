<?php

namespace App\Models\Concerns;

use App\Modules\Media\Models\MediaAsset;
use App\Modules\Media\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithMedia
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'attachable');
    }

    public function attachMedia(MediaAsset $asset, string $collection = 'default'): void
    {
        $this->mediaAttachments()->create([
            'media_asset_id' => $asset->getKey(),
            'collection' => $collection,
        ]);
    }
}
