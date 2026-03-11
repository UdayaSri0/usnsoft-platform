<?php

namespace App\Modules\Products\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Models\Concerns\HasApprovalWorkflow;
use App\Models\Concerns\IsPublishable;
use App\Modules\Products\Enums\ProductDownloadVisibility;
use App\Modules\Products\Enums\ProductKind;
use App\Modules\Products\Enums\ProductPricingMode;
use App\Modules\Products\Enums\ProductVisibility;
use App\Modules\Seo\Models\SeoMeta;
use App\Modules\Workflow\Models\ApprovalRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVersion extends Model implements Publishable, RequiresApproval
{
    use HasApprovalWorkflow;
    use HasFactory;
    use IsPublishable;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'version_number',
        'product_category_id',
        'name',
        'slug',
        'product_kind',
        'short_description',
        'full_description',
        'rich_body',
        'featured_flag',
        'product_visibility',
        'download_visibility',
        'pricing_mode',
        'pricing_text',
        'current_version',
        'release_notes',
        'changelog',
        'documentation_link',
        'github_link',
        'support_contact',
        'video_url',
        'featured_image_media_id',
        'release_notes_visible',
        'changelog_visible',
        'reviews_enabled',
        'review_requires_verification',
        'workflow_state',
        'approval_state',
        'change_notes',
        'preview_token_hash',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'scheduled_publish_at',
        'scheduled_unpublish_at',
        'archived_by',
        'archived_at',
        'based_on_version_id',
        'preview_confirmed_by',
        'preview_confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'product_kind' => ProductKind::class,
            'product_visibility' => ProductVisibility::class,
            'download_visibility' => ProductDownloadVisibility::class,
            'pricing_mode' => ProductPricingMode::class,
            'featured_flag' => 'boolean',
            'release_notes_visible' => 'boolean',
            'changelog_visible' => 'boolean',
            'reviews_enabled' => 'boolean',
            'review_requires_verification' => 'boolean',
            'workflow_state' => ContentWorkflowState::class,
            'approval_state' => ApprovalState::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'scheduled_publish_at' => 'datetime',
            'scheduled_unpublish_at' => 'datetime',
            'archived_at' => 'datetime',
            'preview_confirmed_at' => 'datetime',
        ];
    }

    public function getWorkflowState(): ?ContentWorkflowState
    {
        return $this->workflow_state;
    }

    public function setWorkflowState(ContentWorkflowState $state): void
    {
        $this->workflow_state = $state;
    }

    public function getApprovalState(): ?ApprovalState
    {
        return $this->approval_state;
    }

    public function setApprovalState(ApprovalState $state): void
    {
        $this->approval_state = $state;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_version_tags')
            ->withTimestamps()
            ->orderBy('product_tags.name');
    }

    public function platforms(): HasMany
    {
        return $this->hasMany(ProductVersionPlatform::class)->orderBy('platform');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_version_related_products', 'product_version_id', 'related_product_id')
            ->withTimestamps();
    }

    public function faqItems(): HasMany
    {
        return $this->hasMany(ProductVersionFaq::class)->where('is_visible', true)->orderBy('sort_order');
    }

    public function allFaqItems(): HasMany
    {
        return $this->hasMany(ProductVersionFaq::class)->orderBy('sort_order');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(ProductVersionScreenshot::class)->orderBy('sort_order');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class)->orderBy('is_primary', 'desc')->orderBy('sort_order');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Media\Models\MediaAsset::class, 'featured_image_media_id');
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function approvalRecords(): MorphMany
    {
        return $this->morphMany(ApprovalRecord::class, 'approvable');
    }

    public function basedOnVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'based_on_version_id');
    }

    public function previewAccessTokens(): HasMany
    {
        return $this->hasMany(ProductPreviewAccessToken::class);
    }
}
