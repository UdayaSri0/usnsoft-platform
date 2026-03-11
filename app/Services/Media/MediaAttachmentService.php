<?php

namespace App\Services\Media;

use App\Contracts\Media\HasMediaAttachments;
use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Model;

class MediaAttachmentService
{
    /**
     * @param  Model&HasMediaAttachments  $attachable
     */
    public function attach(Model $attachable, MediaAsset $asset, string $collection = 'default', bool $isPrimary = false): void
    {
        $attachable->mediaAttachments()->create([
            'media_asset_id' => $asset->getKey(),
            'collection' => $collection,
            'is_primary' => $isPrimary,
        ]);
    }

    /**
     * @param  Model&HasMediaAttachments  $attachable
     */
    public function detach(Model $attachable, MediaAsset $asset): void
    {
        $attachable->mediaAttachments()->where('media_asset_id', $asset->getKey())->delete();
    }
}
