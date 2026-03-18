<?php

namespace App\Modules\Products\Models;

use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\User;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Media\Models\MediaAsset;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductReviewState;
use App\Modules\Products\Enums\ProductVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name_current',
        'slug_current',
        'short_description_current',
        'product_kind',
        'visibility',
        'featured_flag',
        'current_version_label',
        'featured_image_media_id',
        'current_draft_version_id',
        'current_published_version_id',
        'approved_review_count',
        'average_rating',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'product_kind' => ProductKind::class,
            'visibility' => ProductVisibility::class,
            'featured_flag' => 'boolean',
            'average_rating' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (self $product): void {
            if (! $product->uuid) {
                $product->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug_current';
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProductVersion::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('status', CommentStatus::Approved->value);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('moderation_state', ProductReviewState::Approved->value);
    }

    public function currentDraftVersion(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'current_draft_version_id');
    }

    public function currentPublishedVersion(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'current_published_version_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'featured_image_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviewVerifications(): HasMany
    {
        return $this->hasMany(ProductUserVerification::class);
    }

    public function downloadAccesses(): HasMany
    {
        return $this->hasMany(ProductDownloadAccess::class);
    }

    public function scopePublicCatalog(Builder $query): Builder
    {
        return $query
            ->where('visibility', ProductVisibility::Public->value)
            ->whereNotNull('current_published_version_id')
            ->whereHas('currentPublishedVersion', function (Builder $versionQuery): void {
                $versionQuery
                    ->where('workflow_state', ContentWorkflowState::Published->value)
                    ->where('approval_state', ApprovalState::Approved->value);
            });
    }

    public function scopePubliclyResolvable(Builder $query): Builder
    {
        return $query
            ->whereIn('visibility', [ProductVisibility::Public->value, ProductVisibility::Unlisted->value])
            ->whereNotNull('current_published_version_id')
            ->whereHas('currentPublishedVersion', function (Builder $versionQuery): void {
                $versionQuery
                    ->where('workflow_state', ContentWorkflowState::Published->value)
                    ->where('approval_state', ApprovalState::Approved->value);
            });
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($term): void {
            $searchQuery
                ->where('name_current', 'like', '%'.$term.'%')
                ->orWhere('short_description_current', 'like', '%'.$term.'%')
                ->orWhere('current_version_label', 'like', '%'.$term.'%');
        });
    }
}
