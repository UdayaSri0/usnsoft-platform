<?php

namespace App\Modules\Products\Models;

use App\Models\User;
use App\Modules\Products\Enums\ProductDownloadMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDownloadAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_version_id',
        'product_download_id',
        'user_id',
        'access_granted',
        'download_mode',
        'denied_reason',
        'ip_address',
        'user_agent',
        'attempted_at',
        'completed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'access_granted' => 'boolean',
            'download_mode' => ProductDownloadMode::class,
            'attempted_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
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

    public function download(): BelongsTo
    {
        return $this->belongsTo(ProductDownload::class, 'product_download_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
