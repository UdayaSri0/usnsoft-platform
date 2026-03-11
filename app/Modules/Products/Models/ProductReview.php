<?php

namespace App\Modules\Products\Models;

use App\Models\User;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Enums\ProductVerificationSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'product_user_verification_id',
        'rating',
        'title',
        'body',
        'moderation_state',
        'verification_source',
        'is_featured',
        'submitted_at',
        'moderated_at',
        'moderated_by',
        'moderation_notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'moderation_state' => ProductReviewState::class,
            'verification_source' => ProductVerificationSource::class,
            'is_featured' => 'boolean',
            'submitted_at' => 'datetime',
            'moderated_at' => 'datetime',
            'published_at' => 'datetime',
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

    public function verification(): BelongsTo
    {
        return $this->belongsTo(ProductUserVerification::class, 'product_user_verification_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('moderation_state', ProductReviewState::Approved->value);
    }
}
