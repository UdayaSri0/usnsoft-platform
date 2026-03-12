<?php

namespace App\Modules\Blog\Models;

use App\Contracts\Approval\RequiresApproval;
use App\Contracts\Publishing\Publishable;
use App\Enums\ApprovalState;
use App\Enums\ContentWorkflowState;
use App\Enums\VisibilityState;
use App\Models\Concerns\HasDirectContentWorkflow;
use App\Models\User;
use App\Modules\Media\Models\MediaAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model implements Publishable, RequiresApproval
{
    use HasDirectContentWorkflow;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id',
        'author_user_id',
        'title',
        'slug',
        'excerpt',
        'featured_image_media_id',
        'content_blocks_json',
        'featured_flag',
        'visibility',
        'workflow_state',
        'approval_state',
        'change_notes',
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
        'preview_confirmed_by',
        'preview_confirmed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'content_blocks_json' => 'array',
            'featured_flag' => 'boolean',
            'visibility' => VisibilityState::class,
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'featured_image_media_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag')->withTimestamps()->orderBy('blog_tags.name');
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'blog_post_related', 'blog_post_id', 'related_blog_post_id')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = is_string($term) ? trim($term) : '';

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $searchQuery) use ($term): void {
            $searchQuery
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('excerpt', 'like', '%'.$term.'%')
                ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', '%'.$term.'%'))
                ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', '%'.$term.'%'));
        });
    }
}
