<?php

namespace App\Modules\Products\Models;

use App\Modules\Media\Models\MediaAsset;
use App\Modules\Products\Enums\ProductDownloadMode;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDownload extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_version_id',
        'label',
        'description',
        'version_label',
        'download_mode',
        'visibility',
        'external_url',
        'media_asset_id',
        'is_primary',
        'review_eligible',
        'sort_order',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'download_mode' => ProductDownloadMode::class,
            'visibility' => ProductDownloadVisibility::class,
            'is_primary' => 'boolean',
            'review_eligible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'product_version_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(ProductDownloadAccess::class);
    }
}
