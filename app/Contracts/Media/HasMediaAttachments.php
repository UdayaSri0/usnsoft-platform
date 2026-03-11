<?php

namespace App\Contracts\Media;

use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasMediaAttachments
{
    public function mediaAttachments(): MorphMany;

    public function attachMedia(MediaAsset $asset, string $collection = 'default'): void;
}
