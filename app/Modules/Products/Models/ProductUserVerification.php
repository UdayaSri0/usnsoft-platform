<?php

namespace App\Modules\Products\Models;

use App\Models\User;
use App\Modules\Products\Enums\ProductVerificationSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUserVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'source',
        'product_download_access_id',
        'verified_by',
        'verified_at',
        'expires_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'source' => ProductVerificationSource::class,
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function downloadAccess(): BelongsTo
    {
        return $this->belongsTo(ProductDownloadAccess::class, 'product_download_access_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $expiresQuery): void {
            $expiresQuery->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
