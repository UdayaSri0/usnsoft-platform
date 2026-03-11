<?php

namespace App\Modules\Products\Models;

use App\Modules\Products\Enums\ProductPlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVersionPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_version_id',
        'platform',
    ];

    protected function casts(): array
    {
        return [
            'platform' => ProductPlatform::class,
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'product_version_id');
    }
}
